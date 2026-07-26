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
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { MembershipTierFormModalComponent } from './membership-tier-form-modal/membership-tier-form-modal.component';

import { SystemSettingStore } from '../admin-settings/system-setting.store';
import { PublicSettingStore } from '@core/services/public-setting.store';

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
    CustomSearchInputComponent,
    CustomSelectComponent,
    PaginationComponent,
    BadgeComponent,
    TooltipDirective,
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
  private readonly systemSettingStore = inject(SystemSettingStore);
  public readonly publicSettingStore = inject(PublicSettingStore);

  tiers = signal<MembershipTier[]>([]);
  isLoading = signal<boolean>(false);
  isUpdatingSystemSetting = signal<boolean>(false);
  updatingTierId = signal<string | null>(null);

  // Search & Filter
  searchQuery = '';
  private readonly _searchQuery = signal('');
  readonly selectedStatusFilter = signal<string>('all');
  readonly currentPage = signal<number>(1);
  readonly limit = 10;

  public get statusOptions() {
    return [
      { label: this.lang.translate('common.all'), value: 'all' },
      { label: this.lang.translate('common.status_active'), value: 'active' },
      { label: this.lang.translate('common.status_inactive'), value: 'inactive' },
    ];
  }

  readonly filteredTiers = computed(() => {
    const q = this._searchQuery().toLowerCase().trim();
    const status = this.selectedStatusFilter();

    let list = this.tiers();

    // Filter by search keyword
    if (q) {
      list = list.filter(t =>
        t.name_vi?.toLowerCase().includes(q) ||
        t.name_en?.toLowerCase().includes(q)
      );
    }

    // Filter by active status
    if (status === 'active') {
      list = list.filter(t => t.is_active);
    } else if (status === 'inactive') {
      list = list.filter(t => !t.is_active);
    }

    // Default sort by level ascending
    list = [...list].sort((a, b) => a.level - b.level);

    return list;
  });

  readonly pagedTiers = computed(() => {
    const page = this.currentPage();
    const start = (page - 1) * this.limit;
    return this.filteredTiers().slice(start, start + this.limit);
  });

  readonly totalPages = computed(() => Math.ceil(this.filteredTiers().length / this.limit));
  readonly totalItems = computed(() => this.filteredTiers().length);

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
    this.publicSettingStore.fetchPublicSettings().subscribe();
    this.loadTiers();
  }

  loadTiers(): void {
    if (!this.publicSettingStore.settings().membership_tiers_feature_enabled) {
      this.tiers.set([]);
      this.isLoading.set(false);
      return;
    }
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

  onSearch(query: string): void {
    this._searchQuery.set(query);
    this.currentPage.set(1);
  }

  fetchPage(page: number): void {
    this.currentPage.set(page);
  }

  getTierName(tier: MembershipTier): string {
    if (!tier) return '';
    return this.lang.currentLang() === 'en' ? (tier.name_en || tier.name_vi) : tier.name_vi;
  }

  public toggleSystemEnable(enabled: boolean): void {
    if (this.isUpdatingSystemSetting()) return;
    this.isUpdatingSystemSetting.set(true);

    this.systemSettingStore.updateSettings({ membership_tiers_enabled: enabled }).subscribe({
      next: () => {
        this.publicSettingStore.clearCache();
        this.publicSettingStore.fetchPublicSettings().subscribe({
          next: () => this.isUpdatingSystemSetting.set(false),
          error: () => this.isUpdatingSystemSetting.set(false),
        });
        const statusText = this.lang.translate(enabled ? 'toast.status_enabled' : 'toast.status_disabled');
        this.toast.showToast(this.lang.translate('toast.system_tier_feature_toggled', { status: statusText }), 'success');
      },
      error: () => {
        this.isUpdatingSystemSetting.set(false);
        this.toast.showToast(this.lang.translate('toast.action_failed'), 'error');
      }
    });
  }

  openCreateModal(): void {
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
    if (!tier || this.updatingTierId() === tier.id) return;
    this.updatingTierId.set(tier.id);

    const newStatus = !tier.is_active;
    this.tierService.updateTier(tier.id, { is_active: newStatus }).subscribe({
      next: () => {
        const statusText = this.lang.translate(newStatus ? 'toast.tier_status_activated' : 'toast.tier_status_deactivated');
        this.toast.showToast(this.lang.translate('toast.tier_status_changed', { status: statusText, name: this.getTierName(tier) }), 'success');
        this.loadTiers();
        this.updatingTierId.set(null);
      },
      error: (err) => {
        this.toast.showToast(err.error?.message || this.lang.translate('toast.action_failed'), 'error');
        this.updatingTierId.set(null);
      },
    });
  }

  deleteTier(tier: MembershipTier): void {
    const tierName = this.getTierName(tier);
    const ref = this.modalService.confirm({
      title: this.lang.translate('admin.delete_tier_title'),
      descriptionHtml: `${this.lang.translate('admin.delete_tier_confirm')} <strong>${tierName}</strong>?`,
      confirmText: this.lang.translate('action.delete'),
      cancelText: this.lang.translate('common.cancel'),
      variant: 'danger',
    });

    ref.afterClosed$.subscribe((confirmed: any) => {
      if (confirmed) {
        this.tierService.deleteTier(tier.id).subscribe({
          next: () => {
            this.toast.showToast(this.lang.translate('toast.tier_deleted', { name: tierName }), 'success');
            this.loadTiers();
          },
          error: (err) => {
            this.toast.showToast(err.error?.message || this.lang.translate('toast.tier_delete_failed'), 'error');
          },
        });
      }
    });
  }
}
