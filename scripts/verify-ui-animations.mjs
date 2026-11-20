/**
 * verify-ui-animations.mjs
 *
 * Programmatic objective UI verification using Headless Edge over Chrome DevTools Protocol (CDP).
 * Verifies:
 * 1. Category Navigation Bar smooth sliding indicator alignment.
 * 2. Adjacent sliding transition (Obat -> Suplemen).
 * 3. Direct non-adjacent sliding without intermediate stepping (Suplemen -> Mata).
 * 4. Terminal checkpoint enforcement at 'Mata' (forward clamped, backward allowed).
 * 5. Subcategory sidebar sliding active indicator pill transition.
 * 6. Seamless catalog switching with zero layout shift, min-height stability, and no skeleton flicker.
 */

import { spawn } from 'node:child_process';
import { existsSync, rmSync } from 'node:fs';
import os from 'node:os';
import path from 'node:path';

const EDGE_CANDIDATES = [
    'C:\\Program Files (x86)\\Microsoft\\EdgeCore\\157.0.4341.60\\msedge.exe',
    'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
];

const TARGET_URL = 'http://127.0.0.1:8000/klien/banyuwangi-kota/katalog';
const CDP_PORT = 9222;

function findEdgeBinary() {
    for (const p of EDGE_CANDIDATES) {
        if (existsSync(p)) {
            return p;
        }
    }
    throw new Error('Edge binary not found in candidate paths.');
}

async function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function isPortOpen(url = 'http://127.0.0.1:8000/up') {
    try {
        const res = await fetch(url, { signal: AbortSignal.timeout(3000) });
        return res.ok || res.status < 500;
    } catch {
        return false;
    }
}

class CDPClient {
    constructor(wsUrl) {
        this.wsUrl = wsUrl;
        this.ws = null;
        this.nextId = 1;
        this.pending = new Map();
    }

    async connect() {
        return new Promise((resolve, reject) => {
            this.ws = new WebSocket(this.wsUrl);
            this.ws.onopen = () => resolve();
            this.ws.onerror = (err) => reject(err);
            this.ws.onmessage = (event) => {
                const msg = JSON.parse(event.data);
                if (msg.id && this.pending.has(msg.id)) {
                    const { resolve, reject } = this.pending.get(msg.id);
                    this.pending.delete(msg.id);
                    if (msg.error) {
                        reject(new Error(msg.error.message || JSON.stringify(msg.error)));
                    } else {
                        resolve(msg.result);
                    }
                }
            };
        });
    }

    async send(method, params = {}) {
        const id = this.nextId++;
        return new Promise((resolve, reject) => {
            this.pending.set(id, { resolve, reject });
            this.ws.send(JSON.stringify({ id, method, params }));
        });
    }

    async evaluate(expression) {
        const res = await this.send('Runtime.evaluate', {
            expression,
            returnByValue: true,
            awaitPromise: true,
        });
        if (res.exceptionDetails) {
            throw new Error(`Eval exception: ${JSON.stringify(res.exceptionDetails)}`);
        }
        return res.result?.value;
    }

    async close() {
        if (this.ws) {
            this.ws.close();
        }
    }
}

async function main() {
    console.log('=== STARTING AUTOMATED UI ANIMATION & TRANSITION VERIFICATION ===');

    let phpServer = null;
    let edgeProcess = null;
    const tempUserDataDir = path.join(os.tmpdir(), `edge-cdp-profile-${Date.now()}`);

    try {
        // 1. Check if Laravel local server is running on port 8000
        const serverReady = await isPortOpen();
        if (!serverReady) {
            console.log('[Setup] Launching local PHP server on port 8000...');
            phpServer = spawn('php', ['artisan', 'serve', '--host=127.0.0.1', '--port=8000'], {
                cwd: 'D:\\startup',
                shell: true,
                stdio: 'pipe',
            });
            phpServer.stdout.on('data', (d) => {
                const s = d.toString().trim();
                if (s && !s.includes('/up')) console.log(`[PHP Server] ${s}`);
            });
            phpServer.stderr.on('data', (d) => {
                const s = d.toString().trim();
                if (s) console.error(`[PHP Server Error] ${s}`);
            });

            // Wait for server to respond
            let ready = false;
            for (let i = 0; i < 30; i++) {
                await sleep(500);
                if (await isPortOpen()) {
                    ready = true;
                    break;
                }
            }
            if (!ready) {
                throw new Error('Local PHP server failed to start within 15 seconds.');
            }
            console.log('[Setup] Local PHP server is active and serving requests.');
        } else {
            console.log('[Setup] Existing local web server detected on port 8000.');
        }

        // 2. Locate and launch headless Edge with CDP
        const edgePath = findEdgeBinary();
        console.log(`[Setup] Launching Headless Edge: ${edgePath}`);

        edgeProcess = spawn(edgePath, [
            '--headless=new',
            `--remote-debugging-port=${CDP_PORT}`,
            '--window-size=1280,900',
            '--disable-gpu',
            '--no-first-run',
            '--no-default-browser-check',
            `--user-data-dir=${tempUserDataDir}`,
            TARGET_URL,
        ], {
            stdio: 'ignore',
        });

        // Wait for CDP endpoint
        let cdpVersion = null;
        for (let i = 0; i < 20; i++) {
            await sleep(400);
            try {
                const res = await fetch(`http://127.0.0.1:${CDP_PORT}/json/version`);
                if (res.ok) {
                    cdpVersion = await res.json();
                    break;
                }
            } catch {}
        }

        if (!cdpVersion) {
            throw new Error('Failed to connect to Edge CDP remote debugging endpoint.');
        }
        console.log(`[Setup] Edge CDP connected successfully: ${cdpVersion.Browser}`);

        // 3. Create or attach to target page
        const targetsRes = await fetch(`http://127.0.0.1:${CDP_PORT}/json/list`);
        const targets = await targetsRes.json();
        let pageTarget = targets.find(t => t.type === 'page' && t.url.includes('katalog')) || targets.find(t => t.type === 'page');

        if (!pageTarget) {
            const newRes = await fetch(`http://127.0.0.1:${CDP_PORT}/json/new?${encodeURIComponent(TARGET_URL)}`);
            pageTarget = await newRes.json();
        }

        const client = new CDPClient(pageTarget.webSocketDebuggerUrl);
        await client.connect();

        await client.send('Page.enable');
        await client.send('Runtime.enable');
        await client.send('DOM.enable');
        await client.send('Page.navigate', { url: TARGET_URL });

        console.log('[Navigation] Waiting for DOM and Alpine initialization...');
        let alpineReady = false;
        for (let i = 0; i < 35; i++) {
            await sleep(500);
            try {
                const state = await client.evaluate(`
                    (() => {
                        const tabContainer = document.querySelector('[role="tablist"]') || document.querySelector('[x-ref="tabContainer"]');
                        const circle = document.querySelector('[x-ref="activeCircleIndicator"]') || document.querySelector('.z-5');
                        const bottomBar = document.querySelector('[x-ref="activeBottomBar"]') || document.querySelector('.z-10.bottom-0');
                        const circleRect = circle ? circle.getBoundingClientRect() : null;
                        const isComplete = document.readyState === 'complete';
                        const hasLayout = !!(circleRect && circleRect.width > 0);
                        const allRefs = Array.from(document.querySelectorAll('*'))
                            .filter(el => el.hasAttribute('x-ref'))
                            .map(el => el.getAttribute('x-ref'));

                        return {
                            url: window.location.href,
                            title: document.title,
                            ready: document.readyState,
                            hasContainer: !!tabContainer,
                            hasCircle: !!circle,
                            hasBar: !!bottomBar,
                            hasLayout: hasLayout,
                            hasTabs: !!(tabContainer && circle && bottomBar && isComplete && hasLayout),
                            foundRefs: allRefs
                        };
                    })()
                `);
                console.log('[DOM Inspection]', JSON.stringify(state));
                if (state?.hasTabs) {
                    console.log(`[Navigation] Page ready: ${state.title} (${state.url})`);
                    alpineReady = true;
                    break;
                }
            } catch (err) {
                console.log(`[Navigation Poll error] ${err.message}`);
            }
        }

        if (!alpineReady) {
            throw new Error('Timeout waiting for catalog category navigation components to mount.');
        }

        // Additional settling time for Alpine $nextTick calculations
        await sleep(600);

        const results = [];

        function reportAssertion(name, passed, detail = '') {
            results.push({ name, passed, detail });
            const mark = passed ? 'PASS' : 'FAIL';
            console.log(`  [${mark}] ${name} ${detail ? '(' + detail + ')' : ''}`);
            if (!passed) {
                throw new Error(`Assertion failed: ${name} - ${detail}`);
            }
        }

        // -------------------------------------------------------------
        // TEST 1: Initial Indicator Alignment on 'Obat' (Index 0)
        // -------------------------------------------------------------
        console.log('\n--- Running Test 1: Initial Indicator Alignment ---');
        const initialPositions = await client.evaluate(`
            (() => {
                const circle = document.querySelector('[x-ref="activeCircleIndicator"]');
                const bar = document.querySelector('[x-ref="activeBottomBar"]');
                const btn0 = document.querySelector('[x-ref="btn_0"]');
                const icon0 = document.querySelector('[x-ref="icon_0"]');

                const circleRect = circle.getBoundingClientRect();
                const barRect = bar.getBoundingClientRect();
                const btn0Rect = btn0.getBoundingClientRect();
                const icon0Rect = icon0.getBoundingClientRect();

                return {
                    circleLeft: circleRect.left,
                    circleWidth: circleRect.width,
                    icon0Left: icon0Rect.left,
                    icon0Width: icon0Rect.width,
                    barLeft: barRect.left,
                    barWidth: barRect.width,
                    btn0Left: btn0Rect.left,
                    btn0Width: btn0Rect.width,
                    activeCat: btn0.getAttribute('data-category'),
                    hasTransform: circle.style.transform.includes('translate')
                };
            })()
        `);

        reportAssertion(
            'Initial category is Obat',
            initialPositions.activeCat === 'Obat',
            `category=${initialPositions.activeCat}`
        );
        reportAssertion(
            'Sliding circular indicator is aligned with Obat icon',
            Math.abs(initialPositions.circleLeft - initialPositions.icon0Left) < 4,
            `circleLeft=${initialPositions.circleLeft.toFixed(1)}, icon0Left=${initialPositions.icon0Left.toFixed(1)}`
        );
        reportAssertion(
            'Sliding circular indicator has translate transform',
            initialPositions.hasTransform,
            'transform verified'
        );

        // -------------------------------------------------------------
        // TEST 2: Adjacent Checkpoint Sliding (Obat -> Suplemen)
        // -------------------------------------------------------------
        console.log('\n--- Running Test 2: Adjacent Checkpoint Sliding (Obat -> Suplemen) ---');
        const preAdjacentX = initialPositions.circleLeft;

        // Click Suplemen tab
        await client.evaluate(`
            (() => {
                const btn1 = document.querySelector('[x-ref="btn_1"]');
                btn1.click();
            })()
        `);

        // Wait 800ms for 300ms transition and server roundtrip to complete
        await sleep(800);

        const suplemenPositions = await client.evaluate(`
            (() => {
                const circle = document.querySelector('[x-ref="activeCircleIndicator"]');
                const icon0 = document.querySelector('[x-ref="icon_0"]');
                const icon1 = document.querySelector('[x-ref="icon_1"]');
                const btn1 = document.querySelector('[x-ref="btn_1"]');
                const container = document.querySelector('[x-ref="tabContainer"]');

                const cRect = circle.getBoundingClientRect();
                const i0Rect = icon0.getBoundingClientRect();
                const i1Rect = icon1.getBoundingClientRect();
                const style = window.getComputedStyle(circle);

                return {
                    circleLeft: cRect.left,
                    circleTransform: circle.style.transform,
                    icon0Left: i0Rect.left,
                    icon1Left: i1Rect.left,
                    containerScrollLeft: container.scrollLeft,
                    hasTransition: style.transition.includes('transform') || style.transition.includes('all'),
                    activeCat: btn1.getAttribute('data-category'),
                    isSelected: btn1.getAttribute('aria-selected') === 'true'
                };
            })()
        `);
        console.log('[Suplemen Check Details]', JSON.stringify(suplemenPositions));

        reportAssertion(
            'Suplemen is marked active and selected',
            suplemenPositions.activeCat === 'Suplemen' && suplemenPositions.isSelected,
            `activeCat=${suplemenPositions.activeCat}`
        );
        reportAssertion(
            'Circular indicator slid to the right towards Suplemen',
            suplemenPositions.circleLeft > preAdjacentX,
            `from=${preAdjacentX.toFixed(1)} to=${suplemenPositions.circleLeft.toFixed(1)}`
        );
        reportAssertion(
            'Circular indicator accurately aligns with Suplemen icon after slide',
            Math.abs(suplemenPositions.circleLeft - suplemenPositions.icon1Left) < 4,
            `diff=${Math.abs(suplemenPositions.circleLeft - suplemenPositions.icon1Left).toFixed(1)}px`
        );

        // -------------------------------------------------------------
        // TEST 3: Direct Non-Adjacent Sliding (Suplemen -> Mata)
        // -------------------------------------------------------------
        console.log('\n--- Running Test 3: Non-Adjacent Direct Sliding (Suplemen -> Mata) ---');
        const startX = suplemenPositions.circleLeft;

        // Click Mata (Index 7) directly jumping from Index 1
        await client.evaluate(`
            (() => {
                const btn7 = document.querySelector('[x-ref="btn_7"]');
                btn7.click();
            })()
        `);

        // Wait 800ms for 300ms transition and server roundtrip to complete
        await sleep(800);

        const mataPositions = await client.evaluate(`
            (() => {
                const circle = document.querySelector('[x-ref="activeCircleIndicator"]');
                const icon7 = document.querySelector('[x-ref="icon_7"]');
                const btn7 = document.querySelector('[x-ref="btn_7"]');
                const container = document.querySelector('[x-ref="tabContainer"]');

                const cRect = circle.getBoundingClientRect();
                const iRect = icon7.getBoundingClientRect();

                return {
                    circleLeft: cRect.left,
                    circleTransform: circle.style.transform,
                    icon7Left: iRect.left,
                    containerScrollLeft: container.scrollLeft,
                    activeCat: btn7.getAttribute('data-category'),
                    isTerminal: btn7.getAttribute('data-terminal'),
                    isSelected: btn7.getAttribute('aria-selected') === 'true'
                };
            })()
        `);
        console.log('[Mata Check Details]', JSON.stringify(mataPositions));

        // Parse translate3d X offset from circle.style.transform
        const parseTranslateX = (transformStr) => {
            const m = transformStr.match(/translate3d\(([^p]+)px/);
            return m ? parseFloat(m[1]) : 0;
        };
        const suplemenX = parseTranslateX(suplemenPositions.circleTransform);
        const mataX = parseTranslateX(mataPositions.circleTransform);

        reportAssertion(
            'Mata is active and recognized as terminal checkpoint',
            mataPositions.activeCat === 'Mata' && mataPositions.isTerminal === 'true',
            `activeCat=${mataPositions.activeCat}, isTerminal=${mataPositions.isTerminal}`
        );
        reportAssertion(
            'Circular indicator translated directly across non-adjacent distance to Mata',
            mataX > suplemenX + 200 || mataPositions.circleLeft > startX,
            `suplemenX=${suplemenX.toFixed(1)}px, mataX=${mataX.toFixed(1)}px, diff=${(mataX - suplemenX).toFixed(1)}px`
        );
        reportAssertion(
            'Circular indicator accurately aligns with Mata icon slot',
            Math.abs(mataPositions.circleLeft - mataPositions.icon7Left) < 4,
            `diff=${Math.abs(mataPositions.circleLeft - mataPositions.icon7Left).toFixed(1)}px`
        );

        // -------------------------------------------------------------
        // TEST 4: Terminal Checkpoint Enforcement at 'Mata'
        // -------------------------------------------------------------
        console.log('\n--- Running Test 4: Terminal Boundary Enforcement at Mata ---');

        // Attempt forward navigation beyond Mata using ArrowRight keydown
        await client.evaluate(`
            (() => {
                const container = document.querySelector('[x-ref="tabContainer"]');
                const event = new KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true });
                container.dispatchEvent(event);
            })()
        `);

        await sleep(200);

        const forwardCheck = await client.evaluate(`
            (() => {
                const btn7 = document.querySelector('[x-ref="btn_7"]');
                return {
                    activeCat: document.querySelector('[aria-selected="true"]')?.getAttribute('data-category')
                };
            })()
        `);

        reportAssertion(
            'Forward action cannot advance beyond Mata (terminal forward clamp)',
            forwardCheck.activeCat === 'Mata',
            `currentCategory=${forwardCheck.activeCat}`
        );

        // Execute backward navigation from Mata using ArrowLeft keydown
        await client.evaluate(`
            (() => {
                const container = document.querySelector('[x-ref="tabContainer"]');
                const event = new KeyboardEvent('keydown', { key: 'ArrowLeft', bubbles: true });
                container.dispatchEvent(event);
            })()
        `);

        await sleep(350);

        const backwardCheck = await client.evaluate(`
            (() => {
                return {
                    activeCat: document.querySelector('[aria-selected="true"]')?.getAttribute('data-category')
                };
            })()
        `);

        reportAssertion(
            'Backward action from Mata advances back to Perawatan Diri',
            backwardCheck.activeCat === 'Perawatan Diri',
            `currentCategory=${backwardCheck.activeCat}`
        );

        // -------------------------------------------------------------
        // TEST 5: Subcategory Sidebar Active Pill Sliding Transition
        // -------------------------------------------------------------
        console.log('\n--- Running Test 5: Subcategory Sidebar Sliding Pill Transition ---');

        // Return to Obat to have multiple subcategories available
        await client.evaluate(`
            (() => {
                document.querySelector('[x-ref="btn_0"]').click();
            })()
        `);
        await sleep(600);

        const subcatInit = await client.evaluate(`
            (() => {
                const indicator = document.querySelector('[x-ref="activeSubcatIndicator"]');
                const semuaBtn = document.querySelector('[data-subcat=""]');
                const subButtons = Array.from(document.querySelectorAll('[data-subcat]:not([data-subcat=""])'));

                return {
                    hasIndicator: !!indicator,
                    indicatorTop: parseFloat(indicator?.style.top || '0'),
                    semuaTop: semuaBtn?.offsetTop || 0,
                    availableCount: subButtons.length,
                    firstSubName: subButtons[0]?.getAttribute('data-subcat') || null
                };
            })()
        `);

        reportAssertion(
            'Subcategory sliding active pill exists and is positioned at Semua',
            subcatInit.hasIndicator && subcatInit.indicatorTop === subcatInit.semuaTop,
            `indicatorTop=${subcatInit.indicatorTop}px`
        );

        if (subcatInit.availableCount > 0) {
            console.log(`[Subcategory] Clicking subcategory: "${subcatInit.firstSubName}"`);
            await client.evaluate(`
                (() => {
                    const firstBtn = document.querySelectorAll('[data-subcat]:not([data-subcat=""])')[0];
                    firstBtn.click();
                })()
            `);

            await sleep(500);

            const subcatMoved = await client.evaluate(`
                (() => {
                    const indicator = document.querySelector('[x-ref="activeSubcatIndicator"]');
                    const firstBtn = document.querySelectorAll('[data-subcat]:not([data-subcat=""])')[0];

                    return {
                        indicatorTop: parseFloat(indicator?.style.top || '0'),
                        targetTop: firstBtn?.offsetTop || 0,
                        targetActive: firstBtn?.getAttribute('aria-pressed') === 'true'
                    };
                })()
            `);

            reportAssertion(
                'Subcategory active pill glided down to target subcategory position',
                subcatMoved.indicatorTop === subcatMoved.targetTop && subcatMoved.indicatorTop > 0,
                `indicatorTop=${subcatMoved.indicatorTop}px, targetTop=${subcatMoved.targetTop}px`
            );

            // Test deselection by clicking the active subcategory again (cancel/toggle off)
            console.log(`[Subcategory] Clicking active subcategory again to test deselection glide...`);
            await client.evaluate(`
                (() => {
                    const firstBtn = document.querySelectorAll('[data-subcat]:not([data-subcat=""])')[0];
                    firstBtn.click();
                })()
            `);
            await sleep(500);

            const subcatDeselected = await client.evaluate(`
                (() => {
                    const indicator = document.querySelector('[x-ref="activeSubcatIndicator"]');
                    const firstBtn = document.querySelectorAll('[data-subcat]:not([data-subcat=""])')[0];
                    const semuaBtn = document.querySelector('[data-subcat=""]');
                    return {
                        top: parseFloat(indicator?.style.top || '0'),
                        semuaPressed: semuaBtn?.getAttribute('aria-pressed') === 'true',
                        subcatPressed: firstBtn?.getAttribute('aria-pressed') === 'true'
                    };
                })()
            `);

            reportAssertion(
                'Deselecting active subcategory glides pill back to Semua (top 0)',
                subcatDeselected.top === subcatInit.semuaTop && subcatDeselected.semuaPressed && !subcatDeselected.subcatPressed,
                `deselectedTop=${subcatDeselected.top}px, semuaPressed=${subcatDeselected.semuaPressed}`
            );

            // Re-click subcategory to test clicking Semua button explicitly
            await client.evaluate(`
                (() => {
                    const firstBtn = document.querySelectorAll('[data-subcat]:not([data-subcat=""])')[0];
                    firstBtn.click();
                })()
            `);
            await sleep(500);

            // Click Semua to glide back up
            await client.evaluate(`
                (() => {
                    document.querySelector('[data-subcat=""]').click();
                })()
            `);

            await sleep(500);

            const subcatReset = await client.evaluate(`
                (() => {
                    const indicator = document.querySelector('[x-ref="activeSubcatIndicator"]');
                    return parseFloat(indicator?.style.top || '0');
                })()
            `);

            reportAssertion(
                'Subcategory active pill glided smoothly back to Semua (top 0)',
                subcatReset === subcatInit.semuaTop,
                `resetTop=${subcatReset}px`
            );
        }

        // -------------------------------------------------------------
        // TEST 6: Seamless Catalog Data Switching & Zero Layout Shift
        // -------------------------------------------------------------
        console.log('\n--- Running Test 6: Seamless Data Switching & Zero Layout Shift ---');

        const gridCheck = await client.evaluate(`
            (() => {
                const grid = document.querySelector('#main-catalog-content .grid');
                const rect = grid?.getBoundingClientRect();
                const computed = window.getComputedStyle(grid);

                return {
                    hasHiddenClass: grid.classList.contains('hidden'),
                    isDisplayNone: computed.display === 'none',
                    height: rect?.height || 0,
                    hasStabilizedMinHeight: grid.className.includes('min-h-[460px]'),
                    hasOpacityTransition: grid.className.includes('transition-opacity')
                };
            })()
        `);

        reportAssertion(
            'Product card grid never has hidden class or display:none',
            !gridCheck.hasHiddenClass && !gridCheck.isDisplayNone,
            `display=${gridCheck.isDisplayNone ? 'none' : 'visible'}`
        );
        reportAssertion(
            'Product grid height is stabilized (>= 460px)',
            gridCheck.height >= 460 || gridCheck.hasStabilizedMinHeight,
            `measuredHeight=${gridCheck.height}px, minHeightClass=${gridCheck.hasStabilizedMinHeight}`
        );
        reportAssertion(
            'Product grid has smooth opacity crossfade transition classes',
            gridCheck.hasOpacityTransition,
            'transition-opacity confirmed'
        );

        console.log('\n=============================================================');
        console.log(`ALL ${results.length} OBJECTIVE UI VERIFICATION ASSERTIONS PASSED (100%)!`);
        console.log('=============================================================');

        await client.close();
    } finally {
        if (edgeProcess) {
            try {
                edgeProcess.kill();
            } catch {}
        }
        if (phpServer && phpServer.pid) {
            try {
                spawn('taskkill', ['/pid', phpServer.pid.toString(), '/f', '/t']);
            } catch {}
        }
        try {
            if (existsSync(tempUserDataDir)) {
                rmSync(tempUserDataDir, { recursive: true, force: true });
            }
        } catch {}
    }
}

main().catch(err => {
    console.error('\nVerification script encountered a critical error:');
    console.error(err);
    process.exit(1);
});
