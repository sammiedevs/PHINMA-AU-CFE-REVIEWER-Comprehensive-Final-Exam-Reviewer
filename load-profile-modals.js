async function loadProfileModals() {
    try {
        // Fetch the profile modals HTML
        const response = await fetch('profile-modals.html');
        if (!response.ok) throw new Error('Failed to load profile modals');
        
        const html = await response.text();
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        // Append styles if not already included
        if (!document.getElementById('profile-modal-styles')) {
            const styles = tempDiv.querySelector('style');
            if (styles) {
                styles.id = 'profile-modal-styles';
                document.head.appendChild(styles.cloneNode(true));
            }
        }

        // Append modals to the body (Fix: Use append instead of replacing entire body)
        document.body.appendChild(tempDiv);

        // Ensure modals are now in the DOM before attaching listeners
        setTimeout(() => {
            attachProfileModalListeners();
        }, 100); // Delay ensures elements are ready
    } catch (error) {
        console.error('Error loading profile modals:', error);
    }
}

function attachProfileModalListeners() {
    console.log('Attaching modal event listeners...');

    // Open profile modal
    document.getElementById('profile-btn')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('profile-popup').style.display = 'flex';
    });

    // Close all modals when clicking close buttons
    document.querySelectorAll('.close-button img').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.profile-modal-container').forEach(modal => {
                modal.style.display = 'none';
            });
        });
    });

    // Close modals when clicking outside of content
    document.querySelectorAll('.profile-modal-container').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
    });

    // ✅ Ensure Edit Profile Modal exists before attaching listener
    setTimeout(() => {
        const editProfileBtn = document.getElementById('edit-profile-btn');
        const editNameInput = document.getElementById('edit-name');
        const editEmailInput = document.getElementById('edit-email');
        const editPhoneInput = document.getElementById('edit-phone');
        const modal = document.getElementById('edit-profile-modal');

        if (editProfileBtn && editNameInput && editEmailInput && editPhoneInput && modal) {
            editProfileBtn.addEventListener('click', function () {
                editNameInput.value = document.getElementById('current-user-name')?.textContent || '';
                editEmailInput.value = document.getElementById('current-user-email')?.textContent || '';
                editPhoneInput.value = document.getElementById('current-user-phone')?.textContent || '';

                modal.style.display = 'flex';
            });
        } else {
            console.error("❌ Edit Profile Modal or its inputs not found! Check if elements exist in profile-modals.html.");
        }
    }, 200); // Slight delay ensures elements are loaded

    // ✅ Change Password button event listener
    const changePasswordBtn = document.getElementById('change-password-btn');
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener('click', function() {
            document.getElementById('profile-popup').style.display = 'none';
            document.getElementById('change-password-modal').style.display = 'flex';
        });
    }
}

// Load modals
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadProfileModals);
} else {
    loadProfileModals();
}
