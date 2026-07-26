import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TableComponent, TableColumn, TableCellDirective } from '@shared/components/table/table.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { FaqService, WarrantyPolicyItem, FaqItem, PolicyCategoryItem } from '@core/services/faq.service';
import { ToastService } from '@core/services/toast.service';
import { LanguageService } from '@core/services/language.service';

@Component({
  selector: 'app-admin-faqs',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    PageHeaderComponent,
    IconComponent,
    TableComponent,
    TableCellDirective,
    CardComponent,
    ButtonComponent,
    BadgeComponent,
    CustomSwitchComponent,
    CustomSelectComponent,
    PaginationComponent,
    TooltipDirective,
    TranslatePipe
  ],
  templateUrl: './admin-faqs.component.html',
  styleUrls: ['./admin-faqs.component.scss']
})
export class AdminFaqsComponent implements OnInit {
  private readonly faqService = inject(FaqService);
  private readonly toast = inject(ToastService);
  public readonly lang = inject(LanguageService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  public activeTab = signal<'policies' | 'categories' | 'faqs'>('categories');
  public isLoading = signal<boolean>(true);
  public isSubmitting = signal<boolean>(false);

  public categories = signal<PolicyCategoryItem[]>([]);
  public policies = signal<WarrantyPolicyItem[]>([]);
  public faqs = signal<FaqItem[]>([]);

  // Pagination states
  public categoryPage = signal<number>(1);
  public readonly categoryPageSize = 10;
  public readonly categoryTotalPages = computed(() => Math.ceil(this.categories().length / this.categoryPageSize) || 1);
  public readonly paginatedCategories = computed(() => {
    const start = (this.categoryPage() - 1) * this.categoryPageSize;
    return this.categories().slice(start, start + this.categoryPageSize);
  });

  public policyPage = signal<number>(1);
  public readonly policyPageSize = 10;
  public readonly policyTotalPages = computed(() => Math.ceil(this.policies().length / this.policyPageSize) || 1);
  public readonly paginatedPolicies = computed(() => {
    const start = (this.policyPage() - 1) * this.policyPageSize;
    return this.policies().slice(start, start + this.policyPageSize);
  });

  public faqPage = signal<number>(1);
  public readonly faqPageSize = 10;
  public readonly faqTotalPages = computed(() => Math.ceil(this.faqs().length / this.faqPageSize) || 1);
  public readonly paginatedFaqs = computed(() => {
    const start = (this.faqPage() - 1) * this.faqPageSize;
    return this.faqs().slice(start, start + this.faqPageSize);
  });

  public updatingCategoryId = signal<number | null>(null);
  public updatingPolicyId = signal<number | null>(null);
  public updatingFaqId = signal<number | null>(null);

  // Policy Category Modal State
  public isCategoryModalOpen = signal<boolean>(false);
  public editingCategory = signal<PolicyCategoryItem | null>(null);
  public categoryForm = signal<{
    key: string;
    name_vi: string;
    name_en: string;
    icon: string;
    sort_order: number;
    is_active: boolean;
  }>({
    key: '',
    name_vi: '',
    name_en: '',
    icon: 'package',
    sort_order: 0,
    is_active: true,
  });

  // Policy Modal state
  public isPolicyModalOpen = signal<boolean>(false);
  public editingPolicy = signal<WarrantyPolicyItem | null>(null);
  public policyForm = signal<{
    category: string;
    type: 'accepted' | 'rejected';
    content_vi: string;
    content_en: string;
    sub_content_vi: string;
    sub_content_en: string;
    tag_vi: string;
    tag_en: string;
    tag_color: string;
    sort_order: number;
    is_active: boolean;
  }>({
    category: 'bm',
    type: 'accepted',
    content_vi: '',
    content_en: '',
    sub_content_vi: '',
    sub_content_en: '',
    tag_vi: '',
    tag_en: '',
    tag_color: 'blue',
    sort_order: 0,
    is_active: true,
  });

  // FAQ Modal state
  public isFaqModalOpen = signal<boolean>(false);
  public editingFaq = signal<FaqItem | null>(null);
  public faqForm = signal<{
    question_vi: string;
    question_en: string;
    answer_vi: string;
    answer_en: string;
    sort_order: number;
    is_active: boolean;
  }>({
    question_vi: '',
    question_en: '',
    answer_vi: '',
    answer_en: '',
    sort_order: 0,
    is_active: true,
  });

  // Delete modal state
  public isDeleteModalOpen = signal<boolean>(false);
  public itemToDelete = signal<{ type: 'policy' | 'faq' | 'category'; id: number; title: string } | null>(null);

  public readonly policyCategoryOptions = computed(() => {
    const list = this.categories();
    if (!list || list.length === 0) {
      return [
        { label: 'Business Manager (BM)', value: 'bm' },
        { label: 'Via / Clone Facebook', value: 'via' }
      ];
    }
    return list.map(c => ({
      label: `${this.lang.currentLang() === 'en' ? c.name_en : c.name_vi} (${c.key})`,
      value: c.key
    }));
  });

  public policyTypeOptions = [
    { label: 'Chấp nhận bảo hành (Accepted)', value: 'accepted' },
    { label: 'Từ chối bảo hành (Rejected)', value: 'rejected' }
  ];

  public tagColorOptions = [
    { label: 'Xanh dương (Blue)', value: 'blue' },
    { label: 'Xanh lá (Emerald)', value: 'emerald' },
    { label: 'Hổ phách (Amber)', value: 'amber' },
    { label: 'Đỏ (Rose)', value: 'rose' }
  ];

  public categoryColumns: TableColumn[] = [
    { key: 'key', label: 'Mã Key (Slug)', sortable: true },
    { key: 'name_vi', label: 'Tên Danh mục (VI)', sortable: true },
    { key: 'name_en', label: 'Tên Danh mục (EN)', sortable: true },
    { key: 'icon', label: 'Icon', sortable: false, align: 'center' },
    { key: 'sort_order', label: 'Thứ tự', sortable: true, align: 'center' },
    { key: 'is_active', label: 'Trạng thái', sortable: true, align: 'center' },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'right' }
  ];

  public policyColumns: TableColumn[] = [
    { key: 'category', label: 'Danh mục', sortable: true },
    { key: 'type', label: 'Loại quy định', sortable: true },
    { key: 'content_vi', label: 'Nội dung (VI)', sortable: false },
    { key: 'content_en', label: 'Nội dung (EN)', sortable: false },
    { key: 'tag', label: 'Nhãn (Tag)', sortable: false },
    { key: 'sort_order', label: 'Thứ tự', sortable: true, align: 'center' },
    { key: 'is_active', label: 'Trạng thái', sortable: true, align: 'center' },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'right' }
  ];

  public faqColumns: TableColumn[] = [
    { key: 'question_vi', label: 'Câu hỏi (VI)', sortable: false },
    { key: 'question_en', label: 'Câu hỏi (EN)', sortable: false },
    { key: 'sort_order', label: 'Thứ tự', sortable: true, align: 'center' },
    { key: 'is_active', label: 'Trạng thái', sortable: true, align: 'center' },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'right' }
  ];

  public ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      const tab = params['tab'];
      if (tab === 'categories' || tab === 'policies' || tab === 'faqs') {
        this.activeTab.set(tab);
      }
    });

    this.loadData();
  }

  public changeTab(tab: 'categories' | 'policies' | 'faqs'): void {
    this.activeTab.set(tab);
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { tab },
      queryParamsHandling: 'merge'
    });
  }

  public loadData(): void {
    this.isLoading.set(true);
    this.faqService.getAdminFaqs().subscribe({
      next: (res) => {
        if (res && res.success) {
          this.categories.set(res.categories || []);
          this.policies.set(res.policies || []);
          this.faqs.set(res.faqs || []);
        }
        this.isLoading.set(false);
      },
      error: (err) => {
        this.toast.showError(err?.error?.message || 'Failed to load FAQ admin data');
        this.isLoading.set(false);
      }
    });
  }

  public getCategoryLabel(key: string): string {
    const cat = this.categories().find(c => c.key === key);
    if (cat) {
      return this.lang.currentLang() === 'en' ? cat.name_en : cat.name_vi;
    }
    if (key === 'bm') return 'Business Manager (BM)';
    if (key === 'via') return 'Via / Clone';
    return key;
  }

  // --- Category Handlers ---
  public openCreateCategoryModal(): void {
    this.editingCategory.set(null);
    this.categoryForm.set({
      key: '',
      name_vi: '',
      name_en: '',
      icon: 'package',
      sort_order: (this.categories().length + 1) * 10,
      is_active: true,
    });
    this.isCategoryModalOpen.set(true);
  }

  public openEditCategoryModal(cat: PolicyCategoryItem): void {
    this.editingCategory.set(cat);
    this.categoryForm.set({
      key: cat.key,
      name_vi: cat.name_vi || '',
      name_en: cat.name_en || '',
      icon: cat.icon || 'package',
      sort_order: cat.sort_order,
      is_active: cat.is_active,
    });
    this.isCategoryModalOpen.set(true);
  }

  public saveCategory(): void {
    if (this.isSubmitting()) return;
    const form = this.categoryForm();
    if (!form.name_vi.trim() || !form.name_en.trim()) {
      this.toast.showError('Vui lòng nhập tên danh mục cả Tiếng Việt và Tiếng Anh!');
      return;
    }

    this.isSubmitting.set(true);
    const editing = this.editingCategory();

    if (editing) {
      this.faqService.updatePolicyCategory(editing.id, form).subscribe({
        next: () => {
          this.toast.showSuccess(this.lang.translate('toast.policy_cat_updated'));
          this.isSubmitting.set(false);
          this.isCategoryModalOpen.set(false);
          this.loadData();
        },
        error: (err) => {
          this.toast.showError(err?.error?.message || 'Cập nhật danh mục thất bại!');
          this.isSubmitting.set(false);
        }
      });
    } else {
      this.faqService.createPolicyCategory(form).subscribe({
        next: () => {
          this.toast.showSuccess(this.lang.translate('toast.policy_cat_created'));
          this.isSubmitting.set(false);
          this.isCategoryModalOpen.set(false);
          this.loadData();
        },
        error: (err) => {
          this.toast.showError(err?.error?.message || 'Tạo danh mục thất bại!');
          this.isSubmitting.set(false);
        }
      });
    }
  }

  public toggleCategoryStatus(cat: PolicyCategoryItem): void {
    if (!cat || this.updatingCategoryId() === cat.id) return;
    this.updatingCategoryId.set(cat.id);

    this.faqService.updatePolicyCategory(cat.id, { is_active: !cat.is_active }).subscribe({
      next: () => {
        this.toast.showSuccess(this.lang.translate('toast.policy_cat_updated'));
        this.updatingCategoryId.set(null);
        this.loadData();
      },
      error: () => {
        this.toast.showError('Cập nhật trạng thái thất bại!');
        this.updatingCategoryId.set(null);
      }
    });
  }

  // --- Policy Handlers ---
  public openCreatePolicyModal(): void {
    this.editingPolicy.set(null);
    const defaultCatKey = this.categories().length > 0 ? this.categories()[0].key : 'bm';
    this.policyForm.set({
      category: defaultCatKey,
      type: 'accepted',
      content_vi: '',
      content_en: '',
      sub_content_vi: '',
      sub_content_en: '',
      tag_vi: '',
      tag_en: '',
      tag_color: 'blue',
      sort_order: (this.policies().length + 1) * 10,
      is_active: true,
    });
    this.isPolicyModalOpen.set(true);
  }

  public openEditPolicyModal(policy: WarrantyPolicyItem): void {
    this.editingPolicy.set(policy);
    this.policyForm.set({
      category: policy.category,
      type: policy.type,
      content_vi: policy.content_vi || '',
      content_en: policy.content_en || '',
      sub_content_vi: policy.sub_content_vi || '',
      sub_content_en: policy.sub_content_en || '',
      tag_vi: policy.tag_vi || '',
      tag_en: policy.tag_en || '',
      tag_color: policy.tag_color || 'blue',
      sort_order: policy.sort_order,
      is_active: policy.is_active,
    });
    this.isPolicyModalOpen.set(true);
  }

  public savePolicy(): void {
    if (this.isSubmitting()) return;
    const form = this.policyForm();
    if (!form.content_vi.trim() || !form.content_en.trim()) {
      this.toast.showError('Vui lòng nhập nội dung cả Tiếng Việt và Tiếng Anh!');
      return;
    }

    this.isSubmitting.set(true);
    const editing = this.editingPolicy();

    if (editing) {
      this.faqService.updatePolicy(editing.id, form).subscribe({
        next: () => {
          this.toast.showSuccess(this.lang.translate('toast.policy_updated'));
          this.isSubmitting.set(false);
          this.isPolicyModalOpen.set(false);
          this.loadData();
        },
        error: (err) => {
          this.toast.showError(err?.error?.message || 'Cập nhật thất bại!');
          this.isSubmitting.set(false);
        }
      });
    } else {
      this.faqService.createPolicy(form).subscribe({
        next: () => {
          this.toast.showSuccess(this.lang.translate('toast.policy_created'));
          this.isSubmitting.set(false);
          this.isPolicyModalOpen.set(false);
          this.loadData();
        },
        error: (err) => {
          this.toast.showError(err?.error?.message || 'Tạo thất bại!');
          this.isSubmitting.set(false);
        }
      });
    }
  }

  public togglePolicyStatus(policy: WarrantyPolicyItem): void {
    if (!policy || this.updatingPolicyId() === policy.id) return;
    this.updatingPolicyId.set(policy.id);

    this.faqService.updatePolicy(policy.id, { is_active: !policy.is_active }).subscribe({
      next: () => {
        this.toast.showSuccess(this.lang.translate('toast.policy_updated'));
        this.updatingPolicyId.set(null);
        this.loadData();
      },
      error: () => {
        this.toast.showError('Cập nhật trạng thái thất bại!');
        this.updatingPolicyId.set(null);
      }
    });
  }

  // --- FAQ Handlers ---
  public openCreateFaqModal(): void {
    this.editingFaq.set(null);
    this.faqForm.set({
      question_vi: '',
      question_en: '',
      answer_vi: '',
      answer_en: '',
      sort_order: (this.faqs().length + 1) * 10,
      is_active: true,
    });
    this.isFaqModalOpen.set(true);
  }

  public openEditFaqModal(faq: FaqItem): void {
    this.editingFaq.set(faq);
    this.faqForm.set({
      question_vi: faq.question_vi || '',
      question_en: faq.question_en || '',
      answer_vi: faq.answer_vi || '',
      answer_en: faq.answer_en || '',
      sort_order: faq.sort_order,
      is_active: faq.is_active,
    });
    this.isFaqModalOpen.set(true);
  }

  public saveFaq(): void {
    if (this.isSubmitting()) return;
    const form = this.faqForm();
    if (!form.question_vi.trim() || !form.question_en.trim() || !form.answer_vi.trim() || !form.answer_en.trim()) {
      this.toast.showError('Vui lòng nhập đầy đủ câu hỏi và câu trả lời 2 ngôn ngữ!');
      return;
    }

    this.isSubmitting.set(true);
    const editing = this.editingFaq();

    if (editing) {
      this.faqService.updateFaq(editing.id, form).subscribe({
        next: () => {
          this.toast.showSuccess(this.lang.translate('toast.faq_updated'));
          this.isSubmitting.set(false);
          this.isFaqModalOpen.set(false);
          this.loadData();
        },
        error: (err) => {
          this.toast.showError(err?.error?.message || 'Cập nhật thất bại!');
          this.isSubmitting.set(false);
        }
      });
    } else {
      this.faqService.createFaq(form).subscribe({
        next: () => {
          this.toast.showSuccess(this.lang.translate('toast.faq_created'));
          this.isSubmitting.set(false);
          this.isFaqModalOpen.set(false);
          this.loadData();
        },
        error: (err) => {
          this.toast.showError(err?.error?.message || 'Tạo thất bại!');
          this.isSubmitting.set(false);
        }
      });
    }
  }

  public toggleFaqStatus(faq: FaqItem): void {
    if (!faq || this.updatingFaqId() === faq.id) return;
    this.updatingFaqId.set(faq.id);

    this.faqService.updateFaq(faq.id, { is_active: !faq.is_active }).subscribe({
      next: () => {
        this.toast.showSuccess(this.lang.translate('toast.faq_updated'));
        this.updatingFaqId.set(null);
        this.loadData();
      },
      error: () => {
        this.toast.showError('Cập nhật trạng thái thất bại!');
        this.updatingFaqId.set(null);
      }
    });
  }

  // --- Delete Handler ---
  public confirmDelete(type: 'policy' | 'faq' | 'category', id: number, title: string): void {
    this.itemToDelete.set({ type, id, title });
    this.isDeleteModalOpen.set(true);
  }

  public executeDelete(): void {
    const item = this.itemToDelete();
    if (!item || this.isSubmitting()) return;

    this.isSubmitting.set(true);

    if (item.type === 'category') {
      this.faqService.deletePolicyCategory(item.id).subscribe({
        next: () => {
          this.toast.showSuccess(this.lang.translate('toast.policy_cat_deleted'));
          this.isSubmitting.set(false);
          this.isDeleteModalOpen.set(false);
          this.loadData();
        },
        error: (err) => {
          this.toast.showError(err?.error?.message || 'Xóa danh mục thất bại! Danh mục có thể đang được sử dụng.');
          this.isSubmitting.set(false);
        }
      });
    } else if (item.type === 'policy') {
      this.faqService.deletePolicy(item.id).subscribe({
        next: () => {
          this.toast.showSuccess(this.lang.translate('toast.policy_deleted'));
          this.isSubmitting.set(false);
          this.isDeleteModalOpen.set(false);
          this.loadData();
        },
        error: () => {
          this.toast.showError('Xóa thất bại!');
          this.isSubmitting.set(false);
        }
      });
    } else {
      this.faqService.deleteFaq(item.id).subscribe({
        next: () => {
          this.toast.showSuccess(this.lang.translate('toast.faq_deleted'));
          this.isSubmitting.set(false);
          this.isDeleteModalOpen.set(false);
          this.loadData();
        },
        error: () => {
          this.toast.showError('Xóa thất bại!');
          this.isSubmitting.set(false);
        }
      });
    }
  }
}
