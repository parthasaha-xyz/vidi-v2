document.addEventListener('DOMContentLoaded', function() {
    const tagInputContainer = document.querySelector('.tag-input-container');
    if (tagInputContainer) {
        const tagTextInput = document.getElementById('tag-text-input');
        const tagsHiddenInput = document.getElementById('tags-input-hidden');
        const chipsWrapper = document.getElementById('tag-chips-wrapper');
        
        // This array holds the current state of the tags
        let tags = [];

        // Function to update the hidden input field with the current tags
        const updateHiddenInput = () => {
            tagsHiddenInput.value = tags.join(',');
        };

        // Function to create a visual "chip" element
        const createChip = (tag) => {
            const chip = document.createElement('div');
            chip.className = 'chip';
            
            const chipText = document.createElement('span');
            chipText.textContent = tag;
            
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'chip-close-btn';
            closeBtn.innerHTML = '&times;';
            
            // Event listener to remove the tag when the close button is clicked
            closeBtn.addEventListener('click', () => {
                tags = tags.filter(t => t !== tag); // Remove from array
                chip.remove(); // Remove from view
                updateHiddenInput(); // Update hidden input
            });
            
            chip.appendChild(chipText);
            chip.appendChild(closeBtn);
            return chip;
        };
        
        // Function to add a new tag
        const addTag = (tag) => {
            const tagName = tag.trim().toLowerCase();
            // Only add if it's not empty and not already in the list
            if (tagName && !tags.includes(tagName)) {
                tags.push(tagName);
                const chipElement = createChip(tagName);
                chipsWrapper.appendChild(chipElement);
                updateHiddenInput();
            }
        };

        // Event listener for the text input
        tagTextInput.addEventListener('keydown', (e) => {
            // Add tag on 'Enter' or ','
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault(); // Prevent form submission or typing a comma
                addTag(tagTextInput.value);
                tagTextInput.value = ''; // Clear the input
            }
        });
        
        // Bonus: Also add tag when user blurs the input
        tagTextInput.addEventListener('blur', () => {
            if (tagTextInput.value) {
                addTag(tagTextInput.value);
                tagTextInput.value = '';
            }
        });

        // --- Logic to pre-populate tags when editing ---
        const existingTags = JSON.parse(tagInputContainer.dataset.existingTags || '[]');
        if (existingTags.length > 0) {
            existingTags.forEach(tag => {
                addTag(tag);
            });
        }
    }



    // Add this inside the DOMContentLoaded listener in admin.js

/**
 * =================================================================
 * ADMIN VIDEO LIST - CARD FILTERING
 * =================================================================
 */
const adminVideoGrid = document.getElementById('admin-video-grid');
if (adminVideoGrid) {
    const categoryFilter = document.getElementById('category-filter');
    const tagFilter = document.getElementById('tag-filter');
    const statusFilter = document.getElementById('status-filter');
    const resetButton = document.getElementById('reset-filters');
    const allVideoCards = document.querySelectorAll('.admin-video-card-wrapper');
    const noResultsMessage = document.getElementById('no-results-message');

    const filterAdminCards = () => {
        const selectedCategory = categoryFilter.value.toLowerCase();
        const selectedTag = tagFilter.value.toLowerCase();
        const selectedStatus = statusFilter.value.toLowerCase();
        let visibleCount = 0;

        allVideoCards.forEach(card => {
            const cardCategory = card.dataset.category.toLowerCase();
            const cardTags = card.dataset.tags.toLowerCase();
            const cardStatus = card.dataset.status.toLowerCase();

            const matchesCategory = selectedCategory === '' || cardCategory === selectedCategory;
            const matchesTag = selectedTag === '' || cardTags.includes(selectedTag);
            const matchesStatus = selectedStatus === '' || cardStatus === selectedStatus;

            if (matchesCategory && matchesTag && matchesStatus) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        noResultsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
    };

    const resetAdminFilters = () => {
        categoryFilter.value = '';
        tagFilter.value = '';
        statusFilter.value = '';
        filterAdminCards();
    };

    // Attach event listeners
    categoryFilter.addEventListener('change', filterAdminCards);
    tagFilter.addEventListener('change', filterAdminCards);
    statusFilter.addEventListener('change', filterAdminCards);
    resetButton.addEventListener('click', resetAdminFilters);
}
});