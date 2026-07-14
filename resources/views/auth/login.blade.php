<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWW ERP - Secure Authentication</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-50 p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8 space-y-6">
        
        <!-- Logo and Heading -->
        <div class="text-center space-y-4 flex flex-col items-center">
            <!-- P-V-W interlocking SVG Logo -->
            <svg class="h-16 w-auto" viewBox="0 0 500 240" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- P -->
                <path d="M40 30h110c40 0 70 20 70 60s-30 60-70 60H90v60H40V30zm50 80h60c15 0 25-8 25-20s-10-20-25-20H90v40z" fill="#1E73BE"/>
                <!-- Gray interlocking V accent -->
                <path d="M120 85l60 90-25 40-60-90 25-40z" fill="#707A8A" opacity="0.9"/>
                <!-- W -->
                <path d="M185 30l35 180h40l25-110 25 110h40l35-180h-45l-20 115-25-115H250l-25 115-20-115h-45z" fill="#1E73BE"/>
            </svg>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Praful Welding Works</h1>
                <p class="text-sm text-slate-500 font-semibold uppercase tracking-wider mt-1">ERP Secure Login Portal</p>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="hidden text-sm p-4 rounded-xl border"></div>

        <!-- Forms -->
        <form id="loginForm" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold text-slate-600 uppercase mb-1">Corporate Email Address</label>
                <input type="email" id="email" name="email" required placeholder="e.g. praful@pww.com"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 transition">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-600 uppercase mb-1">Access Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 transition">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center text-slate-500 font-medium">
                    <input type="checkbox" name="remember" class="mr-1.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Remember this device
                </label>
                <span class="text-slate-400">Default: admin123</span>
            </div>

            <button type="submit" id="submitBtn"
                    class="w-full bg-[#1E73BE] hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition duration-150 ease-in-out text-sm flex items-center justify-center space-x-2">
                <span>Sign In to Dashboard</span>
            </button>
        </form>
        
        <div class="text-center text-xs text-slate-400 mt-4">
            Restricted access portal. Registered PWW accounts only.
        </div>
    </div>

    <!-- AJAX Script -->
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const alertContainer = document.getElementById('alertContainer');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const csrfToken = document.querySelector('input[name="_token"]').value;
            
            // UI Loading state
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75');
            submitBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 mr-3 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Authenticating...</span>
            `;
            
            alertContainer.className = 'hidden';
            
            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    alertContainer.className = 'bg-emerald-50 border-emerald-200 text-emerald-800 p-4 rounded-xl border text-sm';
                    alertContainer.innerHTML = `<strong>Success:</strong> ${data.message}`;
                    
                    // Redirect
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    displayErrors(data.errors || ['Invalid credentials. Please try again.']);
                    resetBtn(submitBtn);
                }
            } catch (err) {
                displayErrors(['A network error occurred. Please verify your connection.']);
                resetBtn(submitBtn);
            }
        });
        
        function displayErrors(errors) {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.className = 'bg-rose-50 border-rose-200 text-rose-800 p-4 rounded-xl border text-sm';
            
            let list = '<ul class="list-disc list-inside space-y-1">';
            errors.forEach(err => {
                list += `<li>${err}</li>`;
            });
            list += '</ul>';
            
            alertContainer.innerHTML = `<strong>Authentication Failed:</strong> ${list}`;
        }
        
        function resetBtn(btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-75');
            btn.innerHTML = '<span>Sign In to Dashboard</span>';
        }
    </script>
</body>
</html>
