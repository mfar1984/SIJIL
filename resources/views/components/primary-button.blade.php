<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center text-center px-4 py-2 bg-blue-700 border border-transparent rounded-[3px] font-medium text-xs text-white uppercase tracking-widest hover:bg-[#2c61b6] focus:bg-[#2c61b6] active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
