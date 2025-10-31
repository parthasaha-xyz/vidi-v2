document.addEventListener('DOMContentLoaded', function () {

    /**
     * =================================================================
     * I. SIDEBAR TOGGLE LOGIC
     * =================================================================
     */
    const sidebar = document.getElementById('sidebar');
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const overlay = document.querySelector('.overlay');
    const toggleSidebar = () => {
        if (sidebar && overlay) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    };
    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }
    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }

    /**
     * =================================================================
     * II. REUSABLE PASSWORD VALIDATION FUNCTION
     * =================================================================
     */
    const setupPasswordValidation = (options) => {
        const form = document.getElementById(options.formId);
        if (!form) return;
        const passwordInput = document.getElementById(options.passwordId);
        const confirmInput = options.confirmId ? document.getElementById(options.confirmId) : null;
        const submitButton = document.getElementById(options.buttonId);
        const criteriaElements = {
            length: document.getElementById(options.criteriaIds.length),
            uppercase: document.getElementById(options.criteriaIds.uppercase),
            lowercase: document.getElementById(options.criteriaIds.lowercase),
            number: document.getElementById(options.criteriaIds.number),
            symbol: document.getElementById(options.criteriaIds.symbol),
            match: options.criteriaIds.match ? document.getElementById(options.criteriaIds.match) : null
        };
        const patterns = { uppercase: /[A-Z]/, lowercase: /[a-z]/, number: /[0-9]/, symbol: /[!@#$%^&*(),.?":{}|<>]/ };
        const validate = () => {
            const password = passwordInput.value;
            let allValid = true;
            allValid = updateCriteria(criteriaElements.length, password.length >= 8) && allValid;
            allValid = updateCriteria(criteriaElements.uppercase, patterns.uppercase.test(password)) && allValid;
            allValid = updateCriteria(criteriaElements.lowercase, patterns.lowercase.test(password)) && allValid;
            allValid = updateCriteria(criteriaElements.number, patterns.number.test(password)) && allValid;
            allValid = updateCriteria(criteriaElements.symbol, patterns.symbol.test(password)) && allValid;
            if (confirmInput && criteriaElements.match) {
                const confirmPassword = confirmInput.value;
                allValid = updateCriteria(criteriaElements.match, password && password === confirmPassword) && allValid;
            }
            submitButton.disabled = !allValid;
        };
        const updateCriteria = (element, isValid) => {
            if (!element) return true;
            const icon = element.querySelector('i');
            if (isValid) {
                element.classList.remove('invalid'); element.classList.add('valid');
                icon.classList.remove('fa-times-circle'); icon.classList.add('fa-check-circle');
                return true;
            } else {
                element.classList.remove('valid'); element.classList.add('invalid');
                icon.classList.remove('fa-check-circle'); icon.classList.add('fa-times-circle');
                return false;
            }
        };
        passwordInput.addEventListener('keyup', validate);
        if (confirmInput) { confirmInput.addEventListener('keyup', validate); }
    };

    /**
     * =================================================================
     * III. DYNAMIC FORM & MODAL LOGIC (Clinics, Schedule, Profile, etc.)
     * =================================================================
     */
    // Add Clinic Modal
    const addClinicForm = document.getElementById('add-clinic-form');
    if (addClinicForm) { /* ... Your existing correct code for this ... */ }
    // Upload Pricing Modal
    const uploadPricingModal = document.getElementById('uploadPricingModal');
    if (uploadPricingModal) { /* ... Your existing correct code for this ... */ }
    // Add Schedule Slot Modal
    const addSlotModal = document.getElementById('addSlotModal');
    if (addSlotModal) { /* ... Your existing correct code for this ... */ }
    // Apply Schedule Modal
    const applyScheduleModal = document.getElementById('applyScheduleModal');
    if (applyScheduleModal) { /* ... Your existing correct code for this ... */ }
    // Profile Photo Preview
    const profilePhotoInput = document.getElementById('profile-photo-input');
    if (profilePhotoInput) { /* ... Your existing correct code for this ... */ }


    /**
     * =================================================================
     * IV. CAMPAIGN BUILDER - STEP 1: INSPIRATION VIDEO SELECTION
     * (This is the single, correct, and complete block for step1_inspiration.php)
     * =================================================================
     */
    const inspirationVideoGrid = document.getElementById('inspiration-video-grid');
    if (inspirationVideoGrid) {
        // --- Get All Elements ---
        const nextStepButton = document.getElementById('next-step-button');
        const allVideoCards = document.querySelectorAll('.inspiration-card-vertical');
        const allVideoCols = document.querySelectorAll('.inspiration-video-col');
        
        // Filter elements
        const searchInput = document.getElementById('video-search-input');
        const categoryCheckboxes = document.querySelectorAll('.category-filter-check');
        const tagPills = document.querySelectorAll('.tag-filter-pill');
        const noResultsMessage = document.getElementById('no-results-message');

        const maxSelections = 3;

        // --- Selection Logic ---
        const checkSelectionCount = () => {
            const selectedCount = document.querySelectorAll('.inspiration-card-vertical.selected').length;
            nextStepButton.disabled = selectedCount === 0;
        };

        allVideoCards.forEach(card => {
            const selectButton = card.querySelector('.select-video-btn');
            const checkbox = card.querySelector('.video-checkbox');
            
            selectButton.addEventListener('click', () => {
                const isSelected = card.classList.contains('selected');
                const selectedCount = document.querySelectorAll('.inspiration-card-vertical.selected').length;

                if (isSelected) {
                    card.classList.remove('selected');
                    selectButton.textContent = 'Select';
                    selectButton.classList.replace('btn-primary', 'btn-outline-primary');
                    checkbox.checked = false;
                } else {
                    if (selectedCount < maxSelections) {
                        card.classList.add('selected');
                        selectButton.textContent = 'Selected';
                        selectButton.classList.replace('btn-outline-primary', 'btn-primary');
                        checkbox.checked = true;
                    } else {
                        alert(`You can select a maximum of ${maxSelections} videos.`);
                    }
                }
                checkSelectionCount();
            });

            const player = videojs(card.querySelector('.video-js'));
            const videoContainer = card.querySelector('.video-container-vertical');
            videoContainer.addEventListener('mouseenter', () => player.play().catch(()=>{}));
            videoContainer.addEventListener('mouseleave', () => player.pause());
        });
        
        checkSelectionCount();
        
        // --- Filtering Logic (REBUILT FOR RELIABILITY) ---
        const filterVideos = () => {
            const searchText = searchInput.value.toLowerCase();
            
            const selectedCategories = Array.from(categoryCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value.toLowerCase());
            
            const activeTag = document.querySelector('.tag-filter-pill.active');
            const selectedTag = activeTag ? activeTag.dataset.tag.toLowerCase() : '';

            let visibleCount = 0;
            allVideoCols.forEach(col => {
                // Use `|| ''` to prevent errors if a data attribute is missing
                const title = (col.dataset.title || '').toLowerCase();
                const description = (col.dataset.description || '').toLowerCase();
                const category = (col.dataset.category || '').toLowerCase();
                const tags = (col.dataset.tags || '').toLowerCase();

                const matchesSearch = searchText === '' || title.includes(searchText) || description.includes(searchText);
                const matchesCategory = selectedCategories.length === 0 || selectedCategories.includes(category);
                const matchesTag = selectedTag === '' || tags.includes(selectedTag);

                if (matchesSearch && matchesCategory && matchesTag) {
                    col.style.display = 'block';
                    visibleCount++;
                } else {
                    col.style.display = 'none';
                }
            });
            noResultsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        };

        // Event listener for tag pills (single selection)
        tagPills.forEach(pill => {
            pill.addEventListener('click', () => {
                // If the clicked pill is already active, deactivate it.
                if (pill.classList.contains('active')) {
                    pill.classList.remove('active');
                } else {
                    // Otherwise, deactivate all other pills and activate this one.
                    tagPills.forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                }
                filterVideos(); // Trigger the filter function
            });
        });

        // Attach all filter event listeners
        searchInput.addEventListener('keyup', filterVideos);
        categoryCheckboxes.forEach(cb => cb.addEventListener('change', filterVideos));
    }

    /**
     * =================================================================
     * V. INITIALIZE PASSWORD VALIDATORS
     * =================================================================
     */
    setupPasswordValidation({
        formId: 'signup-form',
        passwordId: 'password',
        confirmId: 'confirm-password',
        buttonId: 'signup-button',
        criteriaIds: { length: 'length', uppercase: 'uppercase', lowercase: 'lowercase', number: 'number', symbol: 'symbol', match: 'match' }
    });
    
    setupPasswordValidation({
        formId: 'add-staff-form',
        passwordId: 'staff-password-modal',
        buttonId: 'add-staff-button',
        criteriaIds: { length: 'length-modal', uppercase: 'uppercase-modal', lowercase: 'lowercase-modal', number: 'number-modal', symbol: 'symbol-modal' }
    });




    /**
 * =================================================================
 * CAMPAIGN BUILDER - STEP 3: INITIALIZE RICH TEXT EDITOR
 * =================================================================
 */
if (document.getElementById('script-editor')) {
    tinymce.init({
        selector: '#script-editor',
        plugins: 'lists link image table code help wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | help',
        height: 300,
        menubar: false,
        setup: function (editor) {
            // This function is called when the editor is initialized
            editor.on('init', function () {
                // You can add custom logic here if needed
            });
        }
    });
}
});