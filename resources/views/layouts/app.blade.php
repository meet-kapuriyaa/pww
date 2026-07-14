<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PWW ERP') - Praful Welding Works</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .active-nav {
            background-color: #1E73BE;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(30, 115, 190, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex bg-slate-50">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col fixed h-full z-30 shadow-sm">
        <!-- Sidebar Brand Header -->
        <div class="px-6 py-5 border-b border-slate-100 flex items-center space-x-3 bg-slate-50/50">
            <!-- P-V-W interlocking SVG Logo -->
            <svg class="h-10 w-auto flex-shrink-0" viewBox="0 0 500 240" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- P -->
                <path d="M40 30h110c40 0 70 20 70 60s-30 60-70 60H90v60H40V30zm50 80h60c15 0 25-8 25-20s-10-20-25-20H90v40z" fill="#1E73BE"/>
                <!-- Gray interlocking V accent -->
                <path d="M120 85l60 90-25 40-60-90 25-40z" fill="#707A8A" opacity="0.9"/>
                <!-- W -->
                <path d="M185 30l35 180h40l25-110 25 110h40l35-180h-45l-20 115-25-115H250l-25 115-20-115h-45z" fill="#1E73BE"/>
            </svg>
            <div class="flex flex-col">
                <span class="text-sm font-black tracking-tight text-slate-800 leading-none">Praful Welding</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Works ERP</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-grow p-4 space-y-3 overflow-y-auto">
            <a href="{{ route('overview') }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 {{ Route::is('overview') ? 'active-nav' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                <span>Overview</span>
            </a>

            <!-- Inventory Section -->
            <div class="space-y-1">
                <span class="px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Inventory</span>
                <a href="{{ route('inventory', ['tab' => 'materials']) }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('inventory') && request('tab', 'materials') === 'materials' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Raw Materials</span>
                </a>
                <a href="{{ route('inventory', ['tab' => 'goods']) }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('inventory') && request('tab') === 'goods' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Finished Goods</span>
                </a>
            </div>

            <!-- Operations Section -->
            <div class="space-y-1">
                <span class="px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Production & Dispatches</span>
                <a href="{{ route('bom') }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('bom') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Bill of Materials (BOM)</span>
                </a>
                <a href="{{ route('production') }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('production') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Production Logs</span>
                </a>
                <a href="{{ route('clients') }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('clients') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Clients & Plants</span>
                </a>
                <a href="{{ route('challans') }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('challans') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                    <span>Delivery Challans</span>
                </a>
            </div>

            <!-- Billing Section -->
            <div class="space-y-1">
                <span class="px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Billing & Finance</span>
                <a href="{{ route('invoices', ['tab' => 'ledger']) }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('invoices') && request('tab', 'ledger') === 'ledger' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Invoices Ledger</span>
                </a>
                <a href="{{ route('invoices', ['tab' => 'challan-converter']) }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('invoices') && request('tab') === 'challan-converter' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Convert Challans</span>
                </a>
                <a href="{{ route('invoices', ['tab' => 'manual-builder']) }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('invoices') && request('tab') === 'manual-builder' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Manual Invoice Builder</span>
                </a>
                <a href="{{ route('employees') }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('employees') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Employees</span>
                </a>
                <a href="{{ route('expenses') }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 {{ Route::is('expenses') ? 'active-nav' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    <span>Expenses Ledger</span>
                </a>
            </div>

            <!-- Reporting Section -->
            <div class="space-y-1">
                <span class="px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Audits</span>
                <a href="{{ route('reports') }}" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 {{ Route::is('reports') ? 'active-nav' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Reports & Export</span>
                </a>
            </div>
        </nav>

        <!-- Sidebar User Profile & Logout -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col space-y-2">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold uppercase">
                    {{ substr(Auth::user()->name ?? 'P', 0, 1) }}
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-sm font-bold text-slate-800 truncate leading-none">{{ Auth::user()->name ?? 'Praful Patel' }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 capitalize mt-1">{{ Auth::user()->role ?? 'Staff' }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span>Sign Out Account</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Pane Wrapper -->
    <div class="flex-grow pl-64 flex flex-col min-h-screen">
        <!-- Toast Notification Area -->
        <div id="globalToast" class="fixed top-5 right-5 z-50 transform translate-y-[-100px] opacity-0 transition-all duration-300 pointer-events-none">
            <div class="bg-white border shadow-xl rounded-xl p-4 flex items-center space-x-3 max-w-sm">
                <div id="toastIcon" class="w-8 h-8 rounded-full flex items-center justify-center"></div>
                <div class="flex-grow">
                    <p id="toastMessage" class="text-sm font-semibold text-slate-800"></p>
                </div>
            </div>
        </div>

        <div class="p-8 flex-grow space-y-6">
            @yield('content')
        </div>
    </div>

    <!-- AJAX Submission Scripts -->
    <script>
        document.addEventListener('submit', async function(e) {
            const form = e.target.closest('.ajax-form');
            if (!form) return;
            
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const alertBox = form.querySelector('.form-alert') || createFormAlert(form);
            const originalBtnHtml = submitBtn.innerHTML;
            
            // Set loading state
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75');
            submitBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 mr-2 text-white inline" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Processing...</span>
            `;
            
            alertBox.className = 'hidden';
            
            const formData = new FormData(form);
            const dataObj = {};
            formData.forEach((value, key) => {
                if (key.endsWith('[]')) {
                    const cleanKey = key.slice(0, -2);
                    if (!dataObj[cleanKey]) dataObj[cleanKey] = [];
                    dataObj[cleanKey].push(value);
                } else if (key.startsWith('labor[')) {
                    // Extract staff ID from name, e.g. labor[3]
                    const staffId = key.match(/\[(.*?)\]/)[1];
                    if (!dataObj['labor']) dataObj['labor'] = {};
                    dataObj['labor'][staffId] = value;
                } else {
                    dataObj[key] = value;
                }
            });
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(dataObj)
                });
                
                const responseData = await response.json();
                
                if (response.ok) {
                    showToast('success', responseData.message || 'Operation completed successfully!');
                    form.reset();
                    
                    // Reload dynamic elements or reload page smoothly
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    const errors = responseData.errors 
                        ? (Array.isArray(responseData.errors) ? responseData.errors : Object.values(responseData.errors).flat())
                        : [responseData.message || 'Validation error. Please verify input.'];
                    
                    displayFormErrors(alertBox, errors);
                    resetSubmitButton(submitBtn, originalBtnHtml);
                }
            } catch (err) {
                console.error(err);
                displayFormErrors(alertBox, ['A system network failure occurred. Please try again.']);
                resetSubmitButton(submitBtn, originalBtnHtml);
            }
        });
        
        function createFormAlert(form) {
            const div = document.createElement('div');
            div.className = 'form-alert hidden text-sm p-4 rounded-xl border mb-4';
            form.insertBefore(div, form.firstChild);
            return div;
        }
        
        function displayFormErrors(alertBox, errors) {
            alertBox.className = 'form-alert bg-rose-50 border-rose-200 text-rose-800 p-4 rounded-xl border text-xs mb-4';
            let list = '<ul class="list-disc list-inside space-y-0.5">';
            errors.forEach(err => {
                list += `<li>${err}</li>`;
            });
            list += '</ul>';
            alertBox.innerHTML = `<strong>Submission Failed:</strong> ${list}`;
        }
        
        function resetSubmitButton(btn, originalHtml) {
            btn.disabled = false;
            btn.classList.remove('opacity-75');
            btn.innerHTML = originalHtml;
        }
        
        function showToast(type, message) {
            const toast = document.getElementById('globalToast');
            const icon = document.getElementById('toastIcon');
            const msgText = document.getElementById('toastMessage');
            
            msgText.innerText = message;
            
            if (type === 'success') {
                icon.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600';
                icon.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
            } else {
                icon.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-rose-100 text-rose-600';
                icon.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
            }
            
            toast.classList.remove('translate-y-[-100px]', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-[-100px]', 'opacity-0');
            }, 3000);
        }
    </script>
</body>
</html>
