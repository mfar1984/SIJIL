<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <x-login-text-input
            type="email"
            name="email"
            id="email"
            label="Email"
            icon="mail"
            required
            autocomplete="username"
            :value="old('email')"
        />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />

        <!-- Password -->
        <x-login-text-input
            type="password"
            name="password"
            id="password"
            label="Password"
            icon="lock"
            required
            autocomplete="current-password"
        />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />

        <div class="mt-4">
            <x-primary-button class="w-full text-center flex items-center justify-center gap-2">
                <span class="material-icons" style="font-size: 18px;">login</span>
                <span>ACCESS</span>
            </x-primary-button>
        </div>
    </form>

    <div class="mt-2 text-[10px] text-gray-400 text-center flex items-center justify-center gap-2" style="letter-spacing:0.05rem;">
        <button onclick="showLegalModal('{{ route('legal.disclaimer') }}')" class="hover:text-primary-DEFAULT transition-colors cursor-pointer">Disclaimer</button>
        <span>•</span>
        <button onclick="showLegalModal('{{ route('legal.privacy') }}')" class="hover:text-primary-DEFAULT transition-colors cursor-pointer">Privacy Policy</button>
        <span>•</span>
        <button onclick="showLegalModal('{{ route('legal.terms') }}')" class="hover:text-primary-DEFAULT transition-colors cursor-pointer">Terms &amp; Conditions</button>
    </div>

    <!-- Legal Modal -->
    <div id="legalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4" onclick="closeLegalModal()">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl animate-slideUp" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gray-50 sticky top-0 z-10">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-900">Loading...</h3>
                <button onclick="closeLegalModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div id="modalContent" class="p-6 overflow-y-auto" style="max-height: calc(90vh - 80px);">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-DEFAULT"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-slideUp {
            animation: slideUp 0.3s ease-out;
        }
    </style>

    <script>
        function showLegalModal(url) {
            const modal = document.getElementById('legalModal');
            const content = document.getElementById('modalContent');
            const title = document.getElementById('modalTitle');
            
            // Show modal with loading state
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            title.textContent = 'Loading...';
            content.innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-DEFAULT"></div>
                </div>
            `;
            
            // Fetch content via AJAX
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    // Parse HTML and extract content from the main container
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Get title from h1
                    const h1 = doc.querySelector('h1');
                    if (h1) {
                        title.textContent = h1.textContent;
                    }
                    
                    // Get main content (everything inside the white bg-white div)
                    const mainContent = doc.querySelector('.prose, .space-y-4');
                    if (mainContent) {
                        content.innerHTML = mainContent.innerHTML;
                    } else {
                        content.innerHTML = '<p class="text-gray-600">Content not available</p>';
                    }
                })
                .catch(error => {
                    content.innerHTML = '<p class="text-red-600">Failed to load content. Please try again.</p>';
                });
        }

        function closeLegalModal() {
            const modal = document.getElementById('legalModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLegalModal();
            }
        });
    </script>
</x-guest-layout>

