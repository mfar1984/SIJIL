import './bootstrap';
import * as malaysiaPostcodes from 'malaysia-postcodes';

// Make malaysiaPostcodes available globally
window.malaysiaPostcodes = malaysiaPostcodes;

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Import Flowbite
import 'flowbite';

// Import intl-tel-input
import 'intl-tel-input/build/css/intlTelInput.css';
import intlTelInput from 'intl-tel-input';

// Initialize intl-tel-input on document ready
document.addEventListener('DOMContentLoaded', function() {
    const phoneInputs = document.querySelectorAll('.phone-input');
    
    if (phoneInputs.length > 0) {
        phoneInputs.forEach(function(input) {
            // Check if we're on the edit page
            const isEditPage = window.location.href.includes('/edit');
            
            // Create a wrapper div for the phone input
            const wrapper = document.createElement('div');
            wrapper.className = 'phone-input-wrapper flex';
            input.parentNode.insertBefore(wrapper, input);
            
            // Create country dropdown
            const countrySelect = document.createElement('select');
            countrySelect.className = 'country-select text-xs border-gray-300 rounded-l-[1px] border-r-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50';
            
            // Add common country codes - grouped by region
            const countryCodes = [
                // ASEAN
                { code: 'my', name: 'Malaysia', dial: '+60', region: 'ASEAN' },
                { code: 'sg', name: 'Singapore', dial: '+65', region: 'ASEAN' },
                { code: 'id', name: 'Indonesia', dial: '+62', region: 'ASEAN' },
                { code: 'th', name: 'Thailand', dial: '+66', region: 'ASEAN' },
                { code: 'ph', name: 'Philippines', dial: '+63', region: 'ASEAN' },
                { code: 'vn', name: 'Vietnam', dial: '+84', region: 'ASEAN' },
                { code: 'bn', name: 'Brunei', dial: '+673', region: 'ASEAN' },
                { code: 'mm', name: 'Myanmar', dial: '+95', region: 'ASEAN' },
                { code: 'kh', name: 'Cambodia', dial: '+855', region: 'ASEAN' },
                { code: 'la', name: 'Laos', dial: '+856', region: 'ASEAN' },
                
                // East Asia
                { code: 'cn', name: 'China', dial: '+86', region: 'East Asia' },
                { code: 'jp', name: 'Japan', dial: '+81', region: 'East Asia' },
                { code: 'kr', name: 'South Korea', dial: '+82', region: 'East Asia' },
                { code: 'tw', name: 'Taiwan', dial: '+886', region: 'East Asia' },
                { code: 'hk', name: 'Hong Kong', dial: '+852', region: 'East Asia' },
                { code: 'mo', name: 'Macau', dial: '+853', region: 'East Asia' },
                { code: 'kp', name: 'North Korea', dial: '+850', region: 'East Asia' },
                { code: 'mn', name: 'Mongolia', dial: '+976', region: 'East Asia' },
                
                // South Asia
                { code: 'in', name: 'India', dial: '+91', region: 'South Asia' },
                { code: 'pk', name: 'Pakistan', dial: '+92', region: 'South Asia' },
                { code: 'bd', name: 'Bangladesh', dial: '+880', region: 'South Asia' },
                { code: 'np', name: 'Nepal', dial: '+977', region: 'South Asia' },
                { code: 'lk', name: 'Sri Lanka', dial: '+94', region: 'South Asia' },
                { code: 'bt', name: 'Bhutan', dial: '+975', region: 'South Asia' },
                { code: 'mv', name: 'Maldives', dial: '+960', region: 'South Asia' },
                { code: 'af', name: 'Afghanistan', dial: '+93', region: 'South Asia' },
                
                // Middle East
                { code: 'sa', name: 'Saudi Arabia', dial: '+966', region: 'Middle East' },
                { code: 'ae', name: 'UAE', dial: '+971', region: 'Middle East' },
                { code: 'qa', name: 'Qatar', dial: '+974', region: 'Middle East' },
                { code: 'kw', name: 'Kuwait', dial: '+965', region: 'Middle East' },
                { code: 'tr', name: 'Turkey', dial: '+90', region: 'Middle East' },
                { code: 'il', name: 'Israel', dial: '+972', region: 'Middle East' },
                { code: 'jo', name: 'Jordan', dial: '+962', region: 'Middle East' },
                { code: 'lb', name: 'Lebanon', dial: '+961', region: 'Middle East' },
                { code: 'om', name: 'Oman', dial: '+968', region: 'Middle East' },
                { code: 'bh', name: 'Bahrain', dial: '+973', region: 'Middle East' },
                { code: 'ir', name: 'Iran', dial: '+98', region: 'Middle East' },
                { code: 'iq', name: 'Iraq', dial: '+964', region: 'Middle East' },
                { code: 'sy', name: 'Syria', dial: '+963', region: 'Middle East' },
                { code: 'ye', name: 'Yemen', dial: '+967', region: 'Middle East' },
                { code: 'ps', name: 'Palestine', dial: '+970', region: 'Middle East' },
                
                // Europe
                { code: 'gb', name: 'United Kingdom', dial: '+44', region: 'Europe' },
                { code: 'de', name: 'Germany', dial: '+49', region: 'Europe' },
                { code: 'fr', name: 'France', dial: '+33', region: 'Europe' },
                { code: 'it', name: 'Italy', dial: '+39', region: 'Europe' },
                { code: 'es', name: 'Spain', dial: '+34', region: 'Europe' },
                { code: 'nl', name: 'Netherlands', dial: '+31', region: 'Europe' },
                { code: 'ch', name: 'Switzerland', dial: '+41', region: 'Europe' },
                { code: 'se', name: 'Sweden', dial: '+46', region: 'Europe' },
                { code: 'no', name: 'Norway', dial: '+47', region: 'Europe' },
                { code: 'dk', name: 'Denmark', dial: '+45', region: 'Europe' },
                { code: 'fi', name: 'Finland', dial: '+358', region: 'Europe' },
                { code: 'ru', name: 'Russia', dial: '+7', region: 'Europe' },
                { code: 'pl', name: 'Poland', dial: '+48', region: 'Europe' },
                { code: 'at', name: 'Austria', dial: '+43', region: 'Europe' },
                { code: 'be', name: 'Belgium', dial: '+32', region: 'Europe' },
                { code: 'ie', name: 'Ireland', dial: '+353', region: 'Europe' },
                { code: 'pt', name: 'Portugal', dial: '+351', region: 'Europe' },
                { code: 'gr', name: 'Greece', dial: '+30', region: 'Europe' },
                { code: 'cz', name: 'Czech Republic', dial: '+420', region: 'Europe' },
                { code: 'ro', name: 'Romania', dial: '+40', region: 'Europe' },
                { code: 'hu', name: 'Hungary', dial: '+36', region: 'Europe' },
                { code: 'bg', name: 'Bulgaria', dial: '+359', region: 'Europe' },
                { code: 'sk', name: 'Slovakia', dial: '+421', region: 'Europe' },
                { code: 'hr', name: 'Croatia', dial: '+385', region: 'Europe' },
                { code: 'lt', name: 'Lithuania', dial: '+370', region: 'Europe' },
                { code: 'si', name: 'Slovenia', dial: '+386', region: 'Europe' },
                { code: 'lv', name: 'Latvia', dial: '+371', region: 'Europe' },
                { code: 'ee', name: 'Estonia', dial: '+372', region: 'Europe' },
                { code: 'cy', name: 'Cyprus', dial: '+357', region: 'Europe' },
                { code: 'lu', name: 'Luxembourg', dial: '+352', region: 'Europe' },
                { code: 'mt', name: 'Malta', dial: '+356', region: 'Europe' },
                { code: 'is', name: 'Iceland', dial: '+354', region: 'Europe' },
                { code: 'al', name: 'Albania', dial: '+355', region: 'Europe' },
                { code: 'mk', name: 'North Macedonia', dial: '+389', region: 'Europe' },
                { code: 'rs', name: 'Serbia', dial: '+381', region: 'Europe' },
                { code: 'me', name: 'Montenegro', dial: '+382', region: 'Europe' },
                { code: 'ba', name: 'Bosnia', dial: '+387', region: 'Europe' },
                { code: 'md', name: 'Moldova', dial: '+373', region: 'Europe' },
                { code: 'by', name: 'Belarus', dial: '+375', region: 'Europe' },
                { code: 'ua', name: 'Ukraine', dial: '+380', region: 'Europe' },
                
                // North America
                { code: 'us', name: 'United States', dial: '+1', region: 'North America' },
                { code: 'ca', name: 'Canada', dial: '+1', region: 'North America' },
                { code: 'mx', name: 'Mexico', dial: '+52', region: 'North America' },
                { code: 'gt', name: 'Guatemala', dial: '+502', region: 'North America' },
                { code: 'bz', name: 'Belize', dial: '+501', region: 'North America' },
                { code: 'sv', name: 'El Salvador', dial: '+503', region: 'North America' },
                { code: 'hn', name: 'Honduras', dial: '+504', region: 'North America' },
                { code: 'ni', name: 'Nicaragua', dial: '+505', region: 'North America' },
                { code: 'cr', name: 'Costa Rica', dial: '+506', region: 'North America' },
                { code: 'pa', name: 'Panama', dial: '+507', region: 'North America' },
                { code: 'bs', name: 'Bahamas', dial: '+1242', region: 'North America' },
                { code: 'cu', name: 'Cuba', dial: '+53', region: 'North America' },
                { code: 'jm', name: 'Jamaica', dial: '+1876', region: 'North America' },
                { code: 'ht', name: 'Haiti', dial: '+509', region: 'North America' },
                { code: 'do', name: 'Dominican Republic', dial: '+1809', region: 'North America' },
                { code: 'pr', name: 'Puerto Rico', dial: '+1787', region: 'North America' },
                
                // South America
                { code: 'br', name: 'Brazil', dial: '+55', region: 'South America' },
                { code: 'ar', name: 'Argentina', dial: '+54', region: 'South America' },
                { code: 'co', name: 'Colombia', dial: '+57', region: 'South America' },
                { code: 'pe', name: 'Peru', dial: '+51', region: 'South America' },
                { code: 've', name: 'Venezuela', dial: '+58', region: 'South America' },
                { code: 'cl', name: 'Chile', dial: '+56', region: 'South America' },
                { code: 'ec', name: 'Ecuador', dial: '+593', region: 'South America' },
                { code: 'bo', name: 'Bolivia', dial: '+591', region: 'South America' },
                { code: 'py', name: 'Paraguay', dial: '+595', region: 'South America' },
                { code: 'uy', name: 'Uruguay', dial: '+598', region: 'South America' },
                { code: 'gy', name: 'Guyana', dial: '+592', region: 'South America' },
                { code: 'sr', name: 'Suriname', dial: '+597', region: 'South America' },
                
                // Oceania
                { code: 'au', name: 'Australia', dial: '+61', region: 'Oceania' },
                { code: 'nz', name: 'New Zealand', dial: '+64', region: 'Oceania' },
                { code: 'pg', name: 'Papua New Guinea', dial: '+675', region: 'Oceania' },
                { code: 'fj', name: 'Fiji', dial: '+679', region: 'Oceania' },
                { code: 'sb', name: 'Solomon Islands', dial: '+677', region: 'Oceania' },
                { code: 'vu', name: 'Vanuatu', dial: '+678', region: 'Oceania' },
                { code: 'ws', name: 'Samoa', dial: '+685', region: 'Oceania' },
                { code: 'to', name: 'Tonga', dial: '+676', region: 'Oceania' },
                
                // Africa
                { code: 'za', name: 'South Africa', dial: '+27', region: 'Africa' },
                { code: 'ng', name: 'Nigeria', dial: '+234', region: 'Africa' },
                { code: 'eg', name: 'Egypt', dial: '+20', region: 'Africa' },
                { code: 'ma', name: 'Morocco', dial: '+212', region: 'Africa' },
                { code: 'ke', name: 'Kenya', dial: '+254', region: 'Africa' },
                { code: 'dz', name: 'Algeria', dial: '+213', region: 'Africa' },
                { code: 'et', name: 'Ethiopia', dial: '+251', region: 'Africa' },
                { code: 'tz', name: 'Tanzania', dial: '+255', region: 'Africa' },
                { code: 'gh', name: 'Ghana', dial: '+233', region: 'Africa' },
                { code: 'cd', name: 'DR Congo', dial: '+243', region: 'Africa' },
                { code: 'ci', name: 'Côte d\'Ivoire', dial: '+225', region: 'Africa' },
                { code: 'ug', name: 'Uganda', dial: '+256', region: 'Africa' },
                { code: 'zm', name: 'Zambia', dial: '+260', region: 'Africa' },
                { code: 'mg', name: 'Madagascar', dial: '+261', region: 'Africa' },
                { code: 'cm', name: 'Cameroon', dial: '+237', region: 'Africa' },
                { code: 'sn', name: 'Senegal', dial: '+221', region: 'Africa' },
                { code: 'ao', name: 'Angola', dial: '+244', region: 'Africa' },
                { code: 'so', name: 'Somalia', dial: '+252', region: 'Africa' },
                { code: 'mz', name: 'Mozambique', dial: '+258', region: 'Africa' },
                { code: 'zw', name: 'Zimbabwe', dial: '+263', region: 'Africa' },
                { code: 'sd', name: 'Sudan', dial: '+249', region: 'Africa' },
                { code: 'rw', name: 'Rwanda', dial: '+250', region: 'Africa' },
                { code: 'ml', name: 'Mali', dial: '+223', region: 'Africa' },
                { code: 'bf', name: 'Burkina Faso', dial: '+226', region: 'Africa' },
                { code: 'ne', name: 'Niger', dial: '+227', region: 'Africa' },
                { code: 'tn', name: 'Tunisia', dial: '+216', region: 'Africa' },
                { code: 'td', name: 'Chad', dial: '+235', region: 'Africa' },
                { code: 'gn', name: 'Guinea', dial: '+224', region: 'Africa' },
                { code: 'bj', name: 'Benin', dial: '+229', region: 'Africa' },
                { code: 'bi', name: 'Burundi', dial: '+257', region: 'Africa' },
                { code: 'ss', name: 'South Sudan', dial: '+211', region: 'Africa' },
                { code: 'tg', name: 'Togo', dial: '+228', region: 'Africa' },
                { code: 'ly', name: 'Libya', dial: '+218', region: 'Africa' },
                { code: 'er', name: 'Eritrea', dial: '+291', region: 'Africa' }
            ];
            
            // Group countries by region
            const regions = {};
            countryCodes.forEach(country => {
                if (!regions[country.region]) {
                    regions[country.region] = [];
                }
                regions[country.region].push(country);
            });
            
            // Sort regions alphabetically, but put ASEAN first
            const sortedRegions = Object.keys(regions).sort((a, b) => {
                if (a === 'ASEAN') return -1;
                if (b === 'ASEAN') return 1;
                return a.localeCompare(b);
            });
            
            // Add options grouped by region
            sortedRegions.forEach(region => {
                const optgroup = document.createElement('optgroup');
                optgroup.label = region;
                
                regions[region].forEach(country => {
                    const option = document.createElement('option');
                    option.value = country.dial.replace('+', '');
                    option.dataset.code = country.code;
                    
                    // Different display format based on page type
                    if (isEditPage) {
                        option.textContent = `${country.name} ${country.dial}`;
                    } else {
                        option.textContent = `${country.name} ${country.dial}`;
                    }
                    
                    if (country.code === 'my') {
                        option.selected = true;
                    }
                    optgroup.appendChild(option);
                });
                countrySelect.appendChild(optgroup);
            });
            
            // Move the input to the wrapper
            wrapper.appendChild(countrySelect);
            input.parentNode.removeChild(input);
            
            // Modify the input class and style
            input.className = 'phone-number-input w-full text-xs border-gray-300 rounded-r-[1px] border-l-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50';
            input.placeholder = 'Phone number';
            wrapper.appendChild(input);
            
            // Add hidden input to store the full number with country code
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = input.name;
            hiddenInput.className = 'phone-hidden-input';
            input.name = input.name + '_display';
            wrapper.appendChild(hiddenInput);
            
            // Update hidden input on change
            function updateHiddenInput() {
                const countryCode = countrySelect.value;
                let phoneNumber = input.value.replace(/\D/g, ''); // Remove non-digits
                
                // If phone number starts with the country code, remove it to prevent duplication
                if (countryCode && phoneNumber.startsWith(countryCode)) {
                    phoneNumber = phoneNumber.substring(countryCode.length);
                }
                
                // Combine country code with phone number
                hiddenInput.value = countryCode + phoneNumber;
            }
            
            input.addEventListener('input', updateHiddenInput);
            countrySelect.addEventListener('change', updateHiddenInput);
            
            // Handle form submission
            const form = input.closest('form');
            if (form) {
                form.addEventListener('submit', updateHiddenInput);
            }
            
            // Initialize with current values
            updateHiddenInput();
        });
    }
});

// Import library secara langsung
import {
  getStates,
  getCities,
  getPostcodes,
} from 'malaysia-postcodes';

// Import country-list-js
import * as countryListJs from 'country-list-js';

// Malaysia Postcodes Integration
document.addEventListener('DOMContentLoaded', function() {
    // Check if address form elements exist on the page
    const stateElement = document.getElementById('state');
    if (stateElement) {
        loadStates();
        
        // Manually attach event listener to ensure it works
        stateElement.addEventListener('change', function() {
            loadCities();
        });
    }
    
    const cityElement = document.getElementById('city');
    if (cityElement) {
        cityElement.addEventListener('change', function() {
            loadPostcodes();
        });
    }
    // Add for org_state and org_city
    const orgStateElement = document.getElementById('org_state');
    if (orgStateElement) {
        orgStateElement.addEventListener('change', function() {
            loadCities('org_state');
        });
    }
    const orgCityElement = document.getElementById('org_city');
    if (orgCityElement) {
        orgCityElement.addEventListener('change', function() {
            loadPostcodes('org_state');
        });
    }
    
    // Load countries if the country dropdown exists
    const countryElement = document.getElementById('country');
    if (countryElement) {
        loadCountries();
    }
    
    // Form submission handler to combine address fields
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            combineAddress();
        });
    }
});

// Load states into dropdown
function loadStates() {
    try {
        const states = getStates();
        const stateSelects = [
            document.getElementById('state'),
            document.getElementById('org_state')
        ].filter(Boolean);
        stateSelects.forEach(stateSelect => {
            if (!stateSelect) return;
            while (stateSelect.options.length > 1) {
                stateSelect.remove(1);
            }
            states.forEach(state => {
                const option = document.createElement('option');
                option.value = state;
                option.textContent = state;
                stateSelect.appendChild(option);
            });
            const othersOption = document.createElement('option');
            othersOption.value = 'others';
            othersOption.textContent = '-- Others --';
            stateSelect.appendChild(othersOption);
            const oldState = stateSelect.getAttribute('data-old-value');
            if (oldState) {
                stateSelect.value = oldState;
                loadCities(stateSelect.id);
            }
        });
    } catch (error) {
        console.error('Error loading states:', error);
    }
}

// Load cities based on selected state
function loadCities(stateId = 'state') {
    try {
        const stateSelect = document.getElementById(stateId);
        const citySelect = document.getElementById(stateId === 'state' ? 'city' : 'org_city');
        const postcodeSelect = document.getElementById(stateId === 'state' ? 'postcode' : 'org_postcode');
        if (!stateSelect || !citySelect || !postcodeSelect) return;
        postcodeSelect.disabled = true;
        while (postcodeSelect.options.length > 1) {
            postcodeSelect.remove(1);
        }
        const selectedState = stateSelect.value;
        if (!selectedState) {
            citySelect.disabled = true;
            while (citySelect.options.length > 1) {
                citySelect.remove(1);
            }
            return;
        }
        const cities = getCities(selectedState);
        while (citySelect.options.length > 1) {
            citySelect.remove(1);
        }
        cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
        citySelect.disabled = false;
        const oldCity = citySelect.getAttribute('data-old-value');
        if (oldCity) {
            citySelect.value = oldCity;
            loadPostcodes(stateId);
        }
    } catch (error) {
        console.error('Error loading cities:', error);
    }
}

// Load postcodes based on selected state and city
function loadPostcodes(stateId = 'state') {
    try {
        const stateSelect = document.getElementById(stateId);
        const citySelect = document.getElementById(stateId === 'state' ? 'city' : 'org_city');
        const postcodeSelect = document.getElementById(stateId === 'state' ? 'postcode' : 'org_postcode');
        if (!stateSelect || !citySelect || !postcodeSelect) return;
        const selectedState = stateSelect.value;
        const selectedCity = citySelect.value;
        if (!selectedState || !selectedCity) {
            postcodeSelect.disabled = true;
            while (postcodeSelect.options.length > 1) {
                postcodeSelect.remove(1);
            }
            return;
        }
        const postcodes = getPostcodes(selectedState, selectedCity);
        while (postcodeSelect.options.length > 1) {
            postcodeSelect.remove(1);
        }
        postcodes.forEach(postcode => {
            const option = document.createElement('option');
            option.value = postcode;
            option.textContent = postcode;
            postcodeSelect.appendChild(option);
        });
        postcodeSelect.disabled = false;
        const oldPostcode = postcodeSelect.getAttribute('data-old-value');
        if (oldPostcode) {
            postcodeSelect.value = oldPostcode;
        }
    } catch (error) {
        console.error('Error loading postcodes:', error);
    }
}

// Load countries into dropdown
function loadCountries() {
    try {
        // Get elements
        const countrySelects = [
            document.getElementById('country'),
            document.getElementById('org_country')
        ].filter(Boolean);
        
        if (!countrySelects.length) {
            return;
        }
        
        // Get all country names as an array
        const countryNames = countryListJs.names();
        if (!countryNames || !Array.isArray(countryNames)) {
            return;
        }
        
        countrySelects.forEach(countrySelect => {
            // Save previously selected value if exists (for edit form)
            const oldValue = countrySelect.getAttribute('data-old-value');
            // Clear existing options
            while (countrySelect.options.length) {
                countrySelect.remove(0);
            }
            // Create default empty option
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = '-- Select Country --';
            countrySelect.appendChild(emptyOption);
            // Sort country names alphabetically
            countryNames.sort();
            // Find Malaysia index in the sorted array
            const malaysiaIndex = countryNames.findIndex(name => name === 'Malaysia');
            // If we have an old value that's not Malaysia, we'll select that instead
            const shouldSelectMalaysia = !oldValue || oldValue === 'Malaysia';
            // Add Malaysia as the first option after the empty option if found
            if (malaysiaIndex >= 0) {
                const malaysiaOption = document.createElement('option');
                malaysiaOption.value = 'Malaysia';
                malaysiaOption.textContent = 'Malaysia';
                if (shouldSelectMalaysia) {
                    malaysiaOption.selected = true;
                }
                countrySelect.appendChild(malaysiaOption);
                // Add all countries except Malaysia
                countryNames.forEach(name => {
                    if (name !== 'Malaysia') {
                        const option = document.createElement('option');
                        option.value = name;
                        option.textContent = name;
                        if (oldValue && oldValue === name) {
                            option.selected = true;
                        }
                        countrySelect.appendChild(option);
                    }
                });
            } else {
                // If Malaysia not found, just add all countries
                countryNames.forEach(name => {
                    const option = document.createElement('option');
                    option.value = name;
                    option.textContent = name;
                    if (oldValue && oldValue === name) {
                        option.selected = true;
                    }
                    countrySelect.appendChild(option);
                });
            }
        });
    } catch (error) {
        console.error('Error loading countries:', error);
    }
}

// Function to toggle manual address fields visibility
function toggleManualAddressFields() {
    const stateValue = document.getElementById('state').value;
    const manualFields = document.getElementById('manual-address-fields');
    const citySelect = document.getElementById('city');
    const postcodeSelect = document.getElementById('postcode');
    
    if (stateValue === 'others') {
        manualFields.classList.remove('hidden');
        citySelect.setAttribute('disabled', true);
        postcodeSelect.setAttribute('disabled', true);
    } else {
        manualFields.classList.add('hidden');
    }
}

// Function to handle state change
function handleStateChange() {
    const stateValue = document.getElementById('state').value;
    
    toggleManualAddressFields();
    
    if (stateValue !== 'others') {
        loadCities();
    }
}

// Combine all address fields into the hidden address field
function combineAddress() {
    const stateValue = document.getElementById('state').value;
    let state, city, postcode;
    
    if (stateValue === 'others') {
        state = document.getElementById('manual_state').value;
        city = document.getElementById('manual_city').value;
        postcode = document.getElementById('manual_postcode').value;
    } else {
        state = stateValue;
        city = document.getElementById('city').value;
        postcode = document.getElementById('postcode').value;
    }
    
    const address1 = document.getElementById('address1').value.trim();
    const address2 = document.getElementById('address2').value.trim();
    const country = document.getElementById('country').value.trim();
    
    // Build the combined address
    let combinedAddress = '';
    
    if (address1) combinedAddress += address1 + '\n';
    if (address2) combinedAddress += address2 + '\n';
    if (city) combinedAddress += city + '\n';
    if (state) combinedAddress += state + '\n';
    if (postcode) combinedAddress += postcode + '\n';
    if (country) combinedAddress += country;
    
    // Set the value of the hidden address field
    document.getElementById('address').value = combinedAddress.trim();
}
