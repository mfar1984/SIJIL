// Malaysia Postcodes Dynamic Loader
let malaysiaData = null;

// Load Malaysia postcodes data
async function loadMalaysiaData() {
    if (malaysiaData) return malaysiaData;
    
    try {
        const response = await fetch('/data/malaysia-postcodes.json');
        const data = await response.json();
        malaysiaData = data;
        return malaysiaData;
    } catch (error) {
        console.error('Error loading Malaysia postcodes data:', error);
        return null;
    }
}

// Get all states
function getStates() {
    if (!malaysiaData || !malaysiaData.state) return [];
    return malaysiaData.state.map(s => s.name).sort();
}

// Get cities for a state
function getCities(stateName) {
    if (!malaysiaData || !malaysiaData.state || !stateName) return [];
    const state = malaysiaData.state.find(s => s.name === stateName);
    if (!state || !state.city) return [];
    return state.city.map(c => c.name).sort();
}

// Get postcodes for a city in a state
function getPostcodes(stateName, cityName) {
    if (!malaysiaData || !malaysiaData.state || !stateName || !cityName) return [];
    const state = malaysiaData.state.find(s => s.name === stateName);
    if (!state || !state.city) return [];
    const city = state.city.find(c => c.name === cityName);
    if (!city || !city.postcode) return [];
    return city.postcode.sort();
}

// Populate state dropdown
function populateStates(selectElement) {
    if (!selectElement) return;
    
    const states = getStates();
    selectElement.innerHTML = '<option value="">-- Select State --</option>';
    
    states.forEach(state => {
        const option = document.createElement('option');
        option.value = state;
        option.textContent = state;
        selectElement.appendChild(option);
    });
}

// Populate city dropdown based on selected state
function populateCities(selectElement, state) {
    if (!selectElement) return;
    
    selectElement.innerHTML = '<option value="">-- Select City --</option>';
    
    if (!state) {
        selectElement.disabled = true;
        return;
    }
    
    const cities = getCities(state);
    selectElement.disabled = false;
    
    cities.forEach(city => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        selectElement.appendChild(option);
    });
}

// Populate postcode dropdown based on selected state and city
function populatePostcodes(selectElement, state, city) {
    if (!selectElement) return;
    
    selectElement.innerHTML = '<option value="">-- Select Postcode --</option>';
    
    if (!state || !city) {
        selectElement.disabled = true;
        return;
    }
    
    const postcodes = getPostcodes(state, city);
    selectElement.disabled = false;
    
    postcodes.forEach(postcode => {
        const option = document.createElement('option');
        option.value = postcode;
        option.textContent = postcode;
        selectElement.appendChild(option);
    });
}

// Initialize Malaysia postcodes for a form
async function initMalaysiaPostcodes(stateId = 'state', cityId = 'city', postcodeId = 'postcode') {
    // Load data first
    await loadMalaysiaData();
    
    const stateSelect = document.getElementById(stateId);
    const citySelect = document.getElementById(cityId);
    const postcodeSelect = document.getElementById(postcodeId);
    
    if (!stateSelect || !citySelect || !postcodeSelect) {
        console.error('State, City, or Postcode select element not found');
        return;
    }
    
    // Populate states
    populateStates(stateSelect);
    
    // Handle state change
    stateSelect.addEventListener('change', function() {
        const selectedState = this.value;
        populateCities(citySelect, selectedState);
        postcodeSelect.innerHTML = '<option value="">-- Select Postcode --</option>';
        postcodeSelect.disabled = true;
        
        // Clear values
        citySelect.value = '';
        postcodeSelect.value = '';
        
        // Trigger Alpine.js update if exists
        if (window.Alpine) {
            citySelect.dispatchEvent(new Event('change'));
            postcodeSelect.dispatchEvent(new Event('change'));
        }
    });
    
    // Handle city change
    citySelect.addEventListener('change', function() {
        const selectedState = stateSelect.value;
        const selectedCity = this.value;
        populatePostcodes(postcodeSelect, selectedState, selectedCity);
        postcodeSelect.value = '';
        
        // Trigger Alpine.js update if exists
        if (window.Alpine) {
            postcodeSelect.dispatchEvent(new Event('change'));
        }
    });
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        loadMalaysiaData,
        getStates,
        getCities,
        getPostcodes,
        populateStates,
        populateCities,
        populatePostcodes,
        initMalaysiaPostcodes
    };
}
