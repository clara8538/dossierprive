/**
 * Bibliotheca — JavaScript principal
 * Onglets admin, modals, toggle auth, recherche
 */

document.addEventListener('DOMContentLoaded', function() {

    // =====================================================
    // FLASH MESSAGES - Auto-dismiss
    // =====================================================
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function(msg) {
        setTimeout(function() {
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-10px)';
            msg.style.transition = 'all 0.3s ease';
            setTimeout(function() { msg.remove(); }, 300);
        }, 4000);
    });

    // =====================================================
    // AUTH PAGE - Toggle Connexion / Inscription
    // =====================================================
    const authTabs = document.querySelectorAll('.auth-tabs button');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    authTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            authTabs.forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');

            var target = tab.getAttribute('data-tab');
            if (target === 'login') {
                if (loginForm) loginForm.classList.remove('hidden');
                if (registerForm) registerForm.classList.add('hidden');
            } else {
                if (loginForm) loginForm.classList.add('hidden');
                if (registerForm) registerForm.classList.remove('hidden');
            }
        });
    });

    // =====================================================
    // ADMIN TABS - Livres / Emprunts / Membres
    // =====================================================
    const adminTabs = document.querySelectorAll('.admin-tabs button');
    const tabContents = document.querySelectorAll('.tab-content');

    adminTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            adminTabs.forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');

            var target = tab.getAttribute('data-tab');
            tabContents.forEach(function(content) {
                content.classList.remove('active');
            });
            var targetEl = document.getElementById('tab-' + target);
            if (targetEl) targetEl.classList.add('active');

            // Mettre à jour l'URL sans recharger
            var url = new URL(window.location.href);
            url.searchParams.set('tab', target);
            window.history.replaceState({}, '', url);
        });
    });

    // =====================================================
    // MODALS
    // =====================================================
    window.openModal = function(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    // Fermer modal en cliquant sur l'overlay
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Fermer modal avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });

    // =====================================================
    // CONFIRM DELETE
    // =====================================================
    window.confirmDelete = function(form, itemName) {
        if (confirm('Êtes-vous sûr de vouloir supprimer "' + itemName + '" ? Cette action est irréversible.')) {
            form.submit();
        }
        return false;
    };

    // =====================================================
    // EDIT LIVRE - Populate modal
    // =====================================================
    window.editLivre = function(id, titre, auteur, categorie, annee, description) {
        document.getElementById('edit-livre-id').value = id;
        document.getElementById('edit-titre').value = titre;
        document.getElementById('edit-auteur').value = auteur;
        document.getElementById('edit-categorie').value = categorie;
        document.getElementById('edit-annee').value = annee;
        document.getElementById('edit-description').value = description || '';
        openModal('modal-edit-livre');
    };

    // =====================================================
    // CATALOGUE FILTERS
    // =====================================================
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var category = btn.getAttribute('data-category');
            var url = new URL(window.location.href);
            if (category === 'all') {
                url.searchParams.delete('categorie');
            } else {
                url.searchParams.set('categorie', category);
            }
            window.location.href = url.toString();
        });
    });

});
