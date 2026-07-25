import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { MembershipTierService, MembershipTier } from '@core/services/membership-tier.service';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-profile-information',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    ButtonComponent,
    IconComponent,
    TranslatePipe,
  ],
  templateUrl: './profile-information.component.html',
  styles: [`
    :host {
      display: block;
    }
  `],
})
export class ProfileInformationComponent implements OnInit {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  private readonly tierService = inject(MembershipTierService);

  public username = '';
  public uid = '';
  public name = '';
  public email = '';
  public phone = '';
  public readonly isSubmitting = signal(false);

  public tiers = signal<MembershipTier[]>([]);
  public nextTier = signal<MembershipTier | null>(null);
  public progressPercent = signal<number>(100);
  public amountNeededForNext = signal<number>(0);

  ngOnInit() {
    const user = this.state.authStore.currentUser();
    if (user) {
      this.username = user.username || '';
      this.uid = user.uid || '';
      this.name = user.name;
      this.email = user.email;
      this.phone = user.phone || '';
    }

    this.loadPublicTiers();
  }

  loadPublicTiers() {
    this.tierService.getPublicTiers().subscribe({
      next: (res) => {
        const publicTiers = res.data || [];
        this.tiers.set(publicTiers);

        const user = this.state.authStore.currentUser();
        const totalDeposited = user?.total_deposited || 0;

        // Find next tier
        const next = publicTiers.find((t) => t.min_deposit > totalDeposited);
        if (next) {
          this.nextTier.set(next);
          const needed = next.min_deposit - totalDeposited;
          this.amountNeededForNext.set(needed);

          // Calculate percentage based on previous tier min deposit vs next tier min deposit
          const currentTier = user?.membership_tier;
          const currentMin = currentTier?.min_deposit || 0;
          const range = next.min_deposit - currentMin;
          const progress = range > 0 ? Math.min(100, Math.max(0, ((totalDeposited - currentMin) / range) * 100)) : 0;
          this.progressPercent.set(progress);
        } else {
          this.nextTier.set(null);
          this.progressPercent.set(100);
        }
      },
    });
  }

  public get currentUserTier(): any {
    return this.state.authStore.currentUser()?.membership_tier || null;
  }

  public getTierName(tier: any): string {
    if (!tier) return '';
    const currentLang = this.lang.currentLang();
    return currentLang === 'en' ? (tier.name_en || tier.name_vi) : tier.name_vi;
  }

  public get currentTotalDeposited(): number {
    return this.state.authStore.currentUser()?.total_deposited || 0;
  }

  public onSave() {
    if (this.isSubmitting()) return;

    if (!this.name) {
      this.state.showToast(this.lang.translate('toast.name_required'), 'error');
      return;
    }

    this.isSubmitting.set(true);
    this.state.authStore.updateProfile(this.name, this.phone)
      .pipe(finalize(() => this.isSubmitting.set(false)))
      .subscribe();
  }
}
