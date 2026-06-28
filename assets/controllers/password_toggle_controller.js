import { Controller } from '@hotwired/stimulus';

/*
 * Contrôleur Stimulus pour basculer l'affichage d'un champ mot de passe.
 *
 * Utilisation dans le template :
 * <div data-controller="password-toggle">
 *     <input type="password" data-password-toggle-target="input">
 *     <button type="button" data-action="password-toggle#toggle" data-password-toggle-target="icon">
 *         <i class="fas fa-eye"></i>
 *     </button>
 * </div>
 */
export default class extends Controller {
    static targets = ['input', 'icon', 'button'];

    toggle() {
        const isPassword = this.inputTarget.type === 'password';

        this.inputTarget.type = isPassword ? 'text' : 'password';

        if (this.hasIconTarget) {
            this.iconTarget.classList.toggle('fa-eye');
            this.iconTarget.classList.toggle('fa-eye-slash');
        }

        if (this.hasButtonTarget) {
            this.buttonTarget.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            this.buttonTarget.setAttribute(
                'aria-label',
                isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'
            );
        }
    }
}
