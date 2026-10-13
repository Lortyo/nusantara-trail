<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Check-in · Nusantara Trail</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @verbatim
    <style>
        [x-cloak] { display: none !important; }
        #reader { position: absolute; inset: 0; }
        #reader video { width: 100% !important; height: 100% !important; object-fit: cover; }
        @keyframes scan { 0%, 100% { top: 10%; } 50% { top: 86%; } }
        .scanline { animation: scan 2.4s ease-in-out infinite; }
    </style>
    @endverbatim
</head>
<body class="font-sans antialiased bg-[#f4f3ee] text-[#0c2016]">

@if(! $event)
    <div class="p-8 text-sm text-gray-500">No events yet. <a href="/admin/events" class="underline">Create one first</a>.</div>
@else
<div x-data="checkin({{ \Illuminate\Support\Js::from($cfg) }})" class="mx-auto max-w-[480px] min-h-screen relative pb-28">

    <header class="bg-[#12392b] text-white px-5 pt-6 pb-5 flex items-start justify-between">
        <div>
            <h1 class="text-[22px] font-semibold leading-tight">Race kit check-in</h1>
            <form method="GET" action="/admin/check-in" class="mt-1">
                <select name="event" onchange="this.form.submit()" class="bg-transparent text-white/70 text-xs focus:outline-none max-w-[260px]">
                    @foreach($events as $e)
                        <option class="text-black" value="{{ $e->getKey() }}" @selected((string) $e->getKey() === (string) $event->getKey())>{{ $e->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <button type="button" @click="help = true" class="text-white/70"><i class="ph ph-question text-2xl"></i></button>
    </header>

    <main class="px-5 py-5">

        <!-- TAB SCANNER -->
        <div x-show="tab === 'scanner'">
            <div class="flex rounded-xl bg-white border border-[#e2e0d8] p-1 text-sm font-semibold">
                <button type="button" @click="setMode('kit')" :class="mode === 'kit' ? 'bg-[#1f5c42] text-white' : 'text-gray-500'" class="flex-1 h-9 rounded-lg">Race kit</button>
                <button type="button" @click="setMode('start')" :class="mode === 'start' ? 'bg-[#1f5c42] text-white' : 'text-gray-500'" class="flex-1 h-9 rounded-lg">Race start</button>
            </div>

            <div class="relative mt-4 rounded-3xl bg-[#17261f] overflow-hidden h-[260px]">
                <div id="reader"></div>

                <div class="absolute inset-0 pointer-events-none">
                    <span class="absolute top-4 left-4 text-[10px] tracking-wide text-white/70">CAMERA · REAR</span>
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-[56%] w-[150px] h-[150px]">
                        <span class="absolute top-0 left-0 w-9 h-9 border-t-4 border-l-4 border-[#d6f26b] rounded-tl-lg"></span>
                        <span class="absolute top-0 right-0 w-9 h-9 border-t-4 border-r-4 border-[#d6f26b] rounded-tr-lg"></span>
                        <span class="absolute bottom-0 left-0 w-9 h-9 border-b-4 border-l-4 border-[#d6f26b] rounded-bl-lg"></span>
                        <span class="absolute bottom-0 right-0 w-9 h-9 border-b-4 border-r-4 border-[#d6f26b] rounded-br-lg"></span>
                        <span class="scanline absolute left-2 right-2 h-px bg-white/70"></span>
                    </div>
                    <p class="absolute bottom-4 inset-x-0 text-center text-xs text-white/70">Align the runner's QR code inside the frame</p>
                </div>

                <button type="button" x-show="torchOk" @click="toggleTorch()" class="absolute top-3 right-4 text-white/80"><i class="ph text-xl" :class="torchOn ? 'ph-flashlight-fill text-[#d6f26b]' : 'ph-flashlight'"></i></button>

                <div x-show="camState !== 'running'" class="absolute inset-0 flex flex-col items-center justify-center text-center px-8 bg-[#17261f] text-white/80 text-sm">
                    <p x-show="camState === 'idle'">Starting camera…</p>
                    <p x-show="camState === 'insecure'">The camera needs a secure (HTTPS) connection. Open this page through an https:// link, or enter the BIB number below.</p>
                    <p x-show="camState === 'denied'">Camera access was blocked. Allow the camera in your browser settings, or enter the BIB number below.</p>
                    <button type="button" x-show="camState === 'denied'" @click="startCamera()" class="mt-3 rounded-lg bg-white/10 px-4 py-2">Try again</button>
                </div>
            </div>

            <form @submit.prevent="submitManual()" class="relative mt-4">
                <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-gray-400 text-xl"></i>
                <input x-model="manual" placeholder="Enter BIB number manually" class="w-full h-12 rounded-xl border border-[#e2e0d8] bg-white pl-12 pr-12 text-[15px] focus:outline-none focus:border-[#1f5c42]">
                <button type="submit" class="absolute right-3 top-2.5 text-[#1f5c42]"><i class="ph ph-arrow-right text-2xl"></i></button>
            </form>

            <!-- Hasil scan -->
            <div x-show="result" x-cloak class="mt-4 rounded-2xl border p-5"
                 :class="{ 'bg-[#e8f1ea] border-[#cfe3d6]': result && result.state === 'success', 'bg-[#fff6e0] border-[#f1dfb8]': result && result.state === 'already', 'bg-[#fdeceb] border-[#f2c9c5]': result && result.state === 'invalid' }">
                <template x-if="result">
                    <div>
                        <div class="flex items-center gap-3">
                            <i class="ph text-3xl" :class="{ 'ph-check-circle text-[#1f5c42]': result.state === 'success', 'ph-warning text-[#a8670f]': result.state === 'already', 'ph-x-circle text-[#b42318]': result.state === 'invalid' }"></i>
                            <p class="text-[22px] font-semibold" :class="{ 'text-[#1f5c42]': result.state === 'success', 'text-[#a8670f]': result.state === 'already', 'text-[#b42318]': result.state === 'invalid' }" x-text="result.title"></p>
                        </div>
                        <p class="text-xl font-semibold mt-4" x-show="result.name" x-text="result.name"></p>
                        <p class="text-sm text-gray-500 mt-1" x-text="result.line"></p>
                        <div class="border-t border-black/10 mt-4 pt-4" x-show="result.detail">
                            <p class="text-sm font-semibold" :class="result.state === 'success' ? 'text-[#1f5c42]' : 'text-gray-700'" x-text="result.detail"></p>
                            <p class="text-sm text-gray-600 mt-1" x-show="result.detail2" x-text="result.detail2"></p>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" x-show="result" x-cloak @click="next()" class="mt-4 w-full h-14 rounded-xl bg-[#1f5c42] hover:bg-[#184c36] text-white font-semibold flex items-center justify-center gap-2">
                <i class="ph ph-scan text-xl"></i> Scan next runner
            </button>

            <p class="text-xs text-gray-500 text-center mt-5 flex items-center justify-center gap-1.5">
                <i class="ph ph-cloud-check text-base"></i> Synced just now · <span x-text="kits"></span> kits collected
            </p>
        </div>

        <!-- TAB HISTORY -->
        <div x-show="tab === 'history'" x-cloak>
            <h2 class="text-lg font-semibold">Recent check-ins</h2>
            <div class="mt-3 divide-y divide-[#e2e0d8] rounded-2xl bg-white border border-[#e2e0d8]">
                <template x-for="h in history" :key="h.type + h.bib + h.ts">
                    <div class="flex items-center justify-between px-4 py-3">
                        <div>
                            <p class="font-semibold text-sm" x-text="h.name"></p>
                            <p class="text-xs text-gray-500">BIB <span x-text="h.bib"></span> · <span x-text="h.category"></span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold" x-text="h.time"></p>
                            <p class="text-[11px] text-gray-500"><span x-text="h.type === 'Kit' ? 'Race kit' : 'Race start'"></span><span x-show="h.by"> · <span x-text="h.by"></span></span></p>
                        </div>
                    </div>
                </template>
                <p x-show="!history.length" class="px-4 py-8 text-sm text-gray-500 text-center">No check-ins yet.</p>
            </div>
        </div>
    </main>

    <!-- Bottom tabs -->
    <nav class="fixed bottom-0 inset-x-0 mx-auto max-w-[480px] bg-white border-t border-[#e2e0d8] flex pb-[env(safe-area-inset-bottom)]">
        <button type="button" @click="tab = 'scanner'" :class="tab === 'scanner' ? 'text-[#1f5c42] font-semibold' : 'text-gray-400'" class="flex-1 py-3 flex flex-col items-center gap-1 text-[11px]"><i class="ph ph-scan text-2xl"></i>Scanner</button>
        <a href="/admin/participants?event={{ $event->getKey() }}" class="flex-1 py-3 flex flex-col items-center gap-1 text-[11px] text-gray-400"><i class="ph ph-users text-2xl"></i>Participants</a>
        <button type="button" @click="tab = 'history'" :class="tab === 'history' ? 'text-[#1f5c42] font-semibold' : 'text-gray-400'" class="flex-1 py-3 flex flex-col items-center gap-1 text-[11px]"><i class="ph ph-clock-counter-clockwise text-2xl"></i>History</button>
    </nav>

    <!-- Bantuan -->
    <div x-show="help" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/40" @click.self="help = false">
        <div class="w-full max-w-[480px] bg-white rounded-t-3xl p-6 text-sm space-y-3">
            <h2 class="text-lg font-semibold">How to check in a runner</h2>
            <p>1. Choose <b>Race kit</b> (kit collection day) or <b>Race start</b> (race morning).</p>
            <p>2. Point the camera at the runner's QR code from the participant portal.</p>
            <p>3. A green card means valid. Amber means the QR was already used. Red means it is not valid.</p>
            <p>4. If the QR cannot be read, type the BIB number in the field below the camera.</p>
            <button type="button" @click="help = false" class="w-full h-11 rounded-xl bg-[#1f5c42] text-white font-semibold">Got it</button>
        </div>
    </div>
</div>

<script>
    function checkin(cfg) {
        return {
            tab: 'scanner', mode: 'kit', event: cfg.event, kits: cfg.kits, history: cfg.history,
            result: null, manual: '', busy: false, help: false,
            camState: 'idle', torchOk: false, torchOn: false, scanner: null, lastCode: '',

            init() { this.startCamera(); },

            async startCamera() {
                if (!window.isSecureContext) { this.camState = 'insecure'; return; }
                try {
                    this.scanner = this.scanner || new Html5Qrcode('reader');
                    await this.scanner.start({ facingMode: 'environment' }, { fps: 10 }, (text) => this.onScan(text), () => {});
                    this.camState = 'running';
                    try { const caps = this.scanner.getRunningTrackCapabilities(); this.torchOk = !!(caps && caps.torch); } catch (e) {}
                } catch (e) {
                    this.camState = 'denied';
                }
            },

            async onScan(text) {
                if (this.busy || text === this.lastCode) return;
                this.lastCode = text;
                await this.submit(text);
            },

            async submit(code) {
                this.busy = true;
                try { if (this.scanner && this.camState === 'running') this.scanner.pause(true); } catch (e) {}

                let data;
                try {
                    const res = await fetch('/admin/check-in/scan', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                        body: JSON.stringify({ event: this.event, code: code, mode: this.mode }),
                    });
                    if (res.status === 419 || res.status === 401) throw new Error('session');
                    data = await res.json();
                    if (!data.state) throw new Error('server');
                } catch (e) {
                    data = e.message === 'session'
                        ? { state: 'invalid', title: 'Session expired', line: 'Reload the page and log in again.' }
                        : { state: 'invalid', title: 'Connection problem', line: 'Could not reach the server. Try again.' };
                }

                this.result = data;
                if (typeof data.kits === 'number') this.kits = data.kits;
                if (data.state === 'success' && data.item) this.history.unshift(data.item);
                if (navigator.vibrate) navigator.vibrate(data.state === 'success' ? 80 : [60, 40, 60]);
                this.busy = false;
            },

            submitManual() {
                const code = this.manual.trim();
                if (!code) return;
                this.manual = '';
                this.lastCode = '';
                this.submit(code);
            },

            next() {
                this.result = null;
                this.lastCode = '';
                try { if (this.scanner && this.camState === 'running') this.scanner.resume(); } catch (e) {}
            },

            setMode(m) { this.mode = m; this.next(); },

            async toggleTorch() {
                try {
                    await this.scanner.applyVideoConstraints({ advanced: [{ torch: !this.torchOn }] });
                    this.torchOn = !this.torchOn;
                } catch (e) {}
            },
        };
    }
</script>
@endif
</body>
</html>