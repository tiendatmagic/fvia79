import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TableComponent, TableColumn, TableCellDirective } from '@shared/components/table/table.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { FaqService, WarrantyPolicyItem, FaqItem } from '@core/services/faq.service';
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
    CustomSwitchComponent,
    CustomSelectComponent,
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

  public activeTab = signal<'policies' | 'faqs'>('policies');
  public isLoading = signal<boolean>(true);
  public isSubmitting = signal<boolean>(false);

  public policies = signal<WarrantyPolicyItem[]>([]);
  public faqs = signal<FaqItem[]>([]);

  // Policy Modal state
  public isPolicyModalOpen = signal<boolean>(false);
  public editingPolicy = signal<WarrantyPolicyItem | null>(null);
  public policyForm = signal<{
    category: 'bm' | 'via';
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
  public itemToDelete = signal<{ type: 'policy' | 'faq'; id: number; title: string } | null>(null);

  public policyCategoryOptions = [
    { label: 'Business Manager (BM)', value: 'bm' },
    { label: 'Via / Clone Facebook', value: 'via' }
  ];

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

  public policyColumns: TableColumn[] = [
    { key: 'category', label: 'Danh mục', sortable: true },
    { key: 'type', label: 'Loại quy định', sortable: true },
    { key: 'content_vi', label: 'Nội dung (VI)', sortable: false },
    { key: 'content_en', label: 'Nội dung (EN)', sortable: false },
    { key: 'tag', label: 'Nhãn (Tag)', sortable: false },
    { key: 'sort_order', label: 'Thứ tự', sortable: true, align: 'center' },
    { key: 'is_active', label: 'Trạng thái', sortable: true, align: 'center' },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'center' }
  ];

  public faqColumns: TableColumn[] = [
    { key: 'question_vi', label: 'Câu hỏi (VI)', sortable: false },
    { key: 'question_en', label: 'Câu hỏi (EN)', sortable: false },
    { key: 'sort_order', label: 'Thứ tự', sortable: true, align: 'center' },
    { key: 'is_active', label: 'Trạng thái', sortable: true, align: 'center' },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'center' }
  ];

  public ngOnInit(): void {
    this.loadData();
  }

  public loadData(): void {
    this.isLoading.set(true);
    this.faqService.getAdminFaqs().subscribe({
      next: (res) => {
        if (res && res.success) {
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

  // --- Policy Handlers ---
  public openCreatePolicyModal(): void {
    this.editingPolicy.set(null);
    this.policyForm.set({
      category: 'bm',
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
    this.faqService.updatePolicy(policy.id, { is_active: !policy.is_active }).subscribe({
      next: () => {
        this.toast.showSuccess(this.lang.translate('toast.policy_updated'));
        this.loadData();
      },
      error: () => this.toast.showError('Cập nhật trạng thái thất bại!')
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
    this.faqService.updateFaq(faq.id, { is_active: !faq.is_active }).subscribe({
      next: () => {
        this.toast.showSuccess(this.lang.translate('toast.faq_updated'));
        this.loadData();
      },
      error: () => this.toast.showError('Cập nhật trạng thái thất bại!')
    });
  }

  // --- Delete Handler ---
  public confirmDelete(type: 'policy' | 'faq', id: number, title: string): void {
    this.itemToDelete.set({ type, id, title });
    this.isDeleteModalOpen.set(true);
  }

  public executeDelete(): void {
    const item = this.itemToDelete();
    if (!item || this.isSubmitting()) return;

    this.isSubmitting.set(true);

    if (item.type === 'policy') {
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
