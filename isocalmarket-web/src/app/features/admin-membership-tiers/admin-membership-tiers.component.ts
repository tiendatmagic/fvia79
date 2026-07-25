import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MembershipTierService, MembershipTier } from '@core/services/membership-tier.service';
import { ToastService } from '@core/services/toast.service';
import { LanguageService } from '@core/services/language.service';
import { ModalService } from '@core/services/modal.service';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';
import { MembershipTierFormModalComponent } from './membership-tier-form-modal/membership-tier-form-modal.component';

@Component({
  selector: 'app-admin-membership-tiers',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    PageHeaderComponent,
    CardComponent,
    ButtonComponent,
    IconComponent,
    CustomSwitchComponent,
    TableComponent,
    TableCellDirective,
    TranslatePipe,
  ],
  templateUrl: './admin-membership-tiers.component.html',
  styleUrls: ['./admin-membership-tiers.component.scss'],
})
export class AdminMembershipTiersComponent implements OnInit {
  private tierService = inject(MembershipTierService);
  private toast = inject(ToastService);
  private modalService = inject(ModalService);
  public lang = inject(LanguageService);

  tiers = signal<MembershipTier[]>([]);
  isLoading = signal<boolean>(false);
  isLocked = signal<boolean>(true);

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'level', label: this.lang.translate('admin.tier_level'), sortable: true, width: '80px' },
      { key: 'name', label: this.lang.translate('admin.tier_name'), sortable: true },
      { key: 'min_deposit', label: this.lang.translate('admin.tier_min_deposit'), sortable: true, align: 'right' },
      { key: 'discount_percent', label: this.lang.translate('admin.tier_discount_percent'), sortable: true, align: 'center' },
      { key: 'users_count', label: this.lang.translate('admin.tier_users_count'), sortable: true, align: 'center' },
      { key: 'is_active', label: this.lang.translate('admin.tier_active_status'), sortable: true, align: 'center' },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' },
    ];
  });

  ngOnInit(): void {
    this.loadTiers();
  }

  loadTiers(): void {
    this.isLoading.set(true);
    this.tierService.getAdminTiers().subscribe({
      next: (res) => {
        this.tiers.set(res.data || []);
        this.isLoading.set(false);
      },
      error: (err) => {
        this.toast.showToast(err.error?.message || 'Failed to load membership tiers.', 'error');
        this.isLoading.set(false);
      },
    });
  }

  toggleLock(): void {
    this.isLocked.update((v) => !v);
  }

  getTierName(tier: MembershipTier): string {
    if (!tier) return '';
    return this.lang.currentLang() === 'en' ? (tier.name_en || tier.name_vi) : tier.name_vi;
  }

  openCreateModal(): void {
    if (this.isLocked()) {
      this.toast.showToast('Vui lòng mở khóa chỉnh sửa trước khi thao tác.', 'warning');
      return;
    }
    const maxLevel = this.tiers().reduce((max, t) => Math.max(max, t.level), 0);
    const ref = this.modalService.open(MembershipTierFormModalComponent, {
      title: this.lang.translate('admin.add_membership_tier'),
      size: 'md',
      closeOnBackdropClick: true,
      data: { maxLevel },
    });

    ref.afterClosed$.subscribe((success: any) => {
      if (success) {
        this.loadTiers();
      }
    });
  }

  openEditModal(tier: MembershipTier): void {
    if (this.isLocked()) {
      this.toast.showToast('Vui lòng mở khóa chỉnh sửa trước khi thao tác.', 'warning');
      return;
    }

    const ref = this.modalService.open(MembershipTierFormModalComponent, {
      title: this.lang.translate('admin.edit_membership_tier'),
      size: 'md',
      closeOnBackdropClick: true,
      data: { tier },
    });

    ref.afterClosed$.subscribe((success: any) => {
      if (success) {
        this.loadTiers();
      }
    });
  }

  toggleActive(tier: MembershipTier): void {
    if (this.isLocked()) {
      this.toast.showToast('Vui lòng mở khóa chỉnh sửa trước khi thao tác.', 'warning');
      return;
    }

    const newStatus = !tier.is_active;
    this.tierService.updateTier(tier.id, { is_active: newStatus }).subscribe({
      next: () => {
        this.toast.showToast(`Đã ${newStatus ? 'kích hoạt' : 'tắt'} cấp bậc ${tier.name_vi}.`, 'success');
        this.loadTiers();
      },
      error: (err) => {
        this.toast.showToast(err.error?.message || 'Thao tác thất bại.', 'error');
      },
    });
  }

  deleteTier(tier: MembershipTier): void {
    if (this.isLocked()) {
      this.toast.showToast('Vui lòng mở khóa chỉnh sửa trước khi thao tác.', 'warning');
      return;
    }

    const tierName = this.getTierName(tier);
    const ref = this.modalService.confirm({
      title: this.lang.translate('admin.delete_tier_title'),
      descriptionHtml: `${this.lang.translate('admin.delete_tier_confirm')} <strong>${tierName}</strong>?`,
      confirmText: this.lang.translate('common.delete'),
      cancelText: this.lang.translate('common.cancel'),
      variant: 'danger',
    });

    ref.afterClosed$.subscribe((confirmed: any) => {
      if (confirmed) {
        this.tierService.deleteTier(tier.id).subscribe({
          next: () => {
            this.toast.showToast(`Đã xóa cấp bậc ${tier.name_vi}.`, 'success');
            this.loadTiers();
          },
          error: (err) => {
            this.toast.showToast(err.error?.message || 'Xóa cấp bậc thất bại.', 'error');
          },
        });
      }
    });
  }
}
