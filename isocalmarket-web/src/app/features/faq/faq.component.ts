import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { FaqService, WarrantyPolicyItem, FaqItem, PolicyCategoryItem } from '@core/services/faq.service';
import { StateService } from '@core/services/state.service';

const DEFAULT_FALLBACK_POLICIES: WarrantyPolicyItem[] = [
  // BM Accepted
  {
    id: 1,
    category: 'bm',
    type: 'accepted',
    content_vi: 'BM bị lỗi hoặc Không nhận được link truy cập ngay sau khi bàn giao',
    content_en: 'BM error or failed to receive access link right after delivery',
    tag_vi: '24 giờ',
    tag_en: '24 hours',
    tag_color: 'blue',
    sort_order: 1,
    is_active: true
  },
  {
    id: 2,
    category: 'bm',
    type: 'accepted',
    content_vi: 'BM bị chết Trước khi KHÁCH HÀNG sử dụng',
    content_en: 'BM dead BEFORE customer usage',
    tag_vi: '24 giờ',
    tag_en: '24 hours',
    tag_color: 'blue',
    sort_order: 2,
    is_active: true
  },
  {
    id: 3,
    category: 'bm',
    type: 'accepted',
    content_vi: 'BM đã xác minh doanh nghiệp (Business Verification)',
    content_en: 'Business Verified BM lost verification status',
    tag_vi: '07 ngày',
    tag_en: '07 days',
    tag_color: 'emerald',
    sort_order: 3,
    is_active: true
  },
  {
    id: 4,
    category: 'bm',
    type: 'accepted',
    content_vi: 'Giới hạn chi tiêu hàng ngày bị giảm — áp dụng cho BM có ghi rõ hoặc thoả thuận ở mô tả sản phẩm',
    content_en: 'Daily spending limit dropped (applicable for BMs explicitly specified in description)',
    tag_vi: '07 ngày',
    tag_en: '07 days',
    tag_color: 'emerald',
    sort_order: 4,
    is_active: true
  },
  // BM Rejected
  {
    id: 5,
    category: 'bm',
    type: 'rejected',
    content_vi: 'BM bị vô hiệu hóa / die sau khi khách đã lên camp quảng cáo hoặc add thẻ thanh toán',
    content_en: 'BM disabled / dead after creating campaigns or adding payment cards',
    tag_vi: 'Từ chối',
    tag_en: 'Rejected',
    tag_color: 'rose',
    sort_order: 1,
    is_active: true
  },
  {
    id: 6,
    category: 'bm',
    type: 'rejected',
    content_vi: 'Quá thời hạn bảo hành quy định (24 giờ đối với BM thường, 07 ngày đối với BM xác minh)',
    content_en: 'Exceeded warranty time limit (24h for normal BM, 07 days for Verified BM)',
    tag_vi: 'Hết hạn',
    tag_en: 'Expired',
    tag_color: 'amber',
    sort_order: 2,
    is_active: true
  },
  {
    id: 7,
    category: 'bm',
    type: 'rejected',
    content_vi: 'Khách hàng tự ý kích out QTV gốc hoặc thay đổi thông tin BM khi chưa kiểm tra thành công',
    content_en: 'Customer removed original admin or changed BM info before verification',
    tag_vi: 'Từ chối',
    tag_en: 'Rejected',
    tag_color: 'rose',
    sort_order: 3,
    is_active: true
  },
  {
    id: 8,
    category: 'bm',
    type: 'rejected',
    content_vi: 'BM bị tụt giới hạn chi tiêu do chạy quảng cáo vi phạm chính sách Facebook hoặc bùng tiền',
    content_en: 'Daily limit reduced due to policy violation ads or unpaid balance',
    tag_vi: 'Vi phạm',
    tag_en: 'Violation',
    tag_color: 'rose',
    sort_order: 4,
    is_active: true
  },
  // Via Accepted
  {
    id: 9,
    category: 'via',
    type: 'accepted',
    content_vi: 'Login lần đầu thất bại: sai UID, sai Mật khẩu, sai mã 2FA, checkpoint 282 sẵn',
    content_en: 'First login failure: wrong UID, wrong Password, wrong 2FA, pre-existing 282 checkpoint',
    tag_vi: 'Lần đầu',
    tag_en: 'First login',
    tag_color: 'amber',
    sort_order: 1,
    is_active: true
  },
  {
    id: 10,
    category: 'via',
    type: 'accepted',
    content_vi: 'Via mô tả "Checkpoint Mail" — bảo hành checkpoint 956 kể cả sau khi khách đã sử dụng',
    content_en: 'Via with "Mail Checkpoint" description — 956 checkpoint warranty after usage',
    sub_content_vi: '(Yêu cầu: chưa thay đổi email, Chưa bật 2FA)',
    sub_content_en: '(Requirement: email unchanged, 2FA not enabled)',
    tag_vi: '07 ngày',
    tag_en: '07 days',
    tag_color: 'emerald',
    sort_order: 2,
    is_active: true
  },
  {
    id: 11,
    category: 'via',
    type: 'accepted',
    content_vi: 'Tài khoản Facebook người dùng thật/Clone bị vô hiệu hóa tài khoản cá nhân, tài khoản trả Trước hoặc hạn chế quảng cáo từ Trước thời điểm mua',
    content_en: 'Personal account disabled or ad restricted prior to purchase time',
    tag_vi: 'Có sẵn',
    tag_en: 'Pre-existing',
    tag_color: 'amber',
    sort_order: 3,
    is_active: true
  },
  {
    id: 12,
    category: 'via',
    type: 'accepted',
    content_vi: 'Thông tin Via Không đúng với mô tả khi mua — vui lòng Không sử dụng để được bảo hành',
    content_en: 'Via specs mismatched with description — please do not use to claim warranty',
    tag_vi: 'Không dùng',
    tag_en: 'Do not use',
    tag_color: 'rose',
    sort_order: 4,
    is_active: true
  },
  // Via Rejected
  {
    id: 13,
    category: 'via',
    type: 'rejected',
    content_vi: 'Tài khoản dính checkpoint / die sau khi khách đã thay đổi Mật khẩu, Email hoặc Mã 2FA',
    content_en: 'Account checkpointed / dead after password, email, or 2FA changed',
    tag_vi: 'Từ chối',
    tag_en: 'Rejected',
    tag_color: 'rose',
    sort_order: 1,
    is_active: true
  },
  {
    id: 14,
    category: 'via',
    type: 'rejected',
    content_vi: 'Quá thời hạn bảo hành mua hàng hiển thị tại mục Lịch Sử Mua Hàng',
    content_en: 'Exceeded purchase warranty duration shown in Order History',
    tag_vi: 'Hết hạn',
    tag_en: 'Expired',
    tag_color: 'amber',
    sort_order: 2,
    is_active: true
  },
  {
    id: 15,
    category: 'via',
    type: 'rejected',
    content_vi: 'Đăng nhập trên thiết bị bẩn, IP đen / Proxy rác bị Facebook quét checkpoint hàng loạt',
    content_en: 'LoggedIn on dirty device, blacklisted IP / poor proxy causing mass checkpoint',
    tag_vi: 'Từ chối',
    tag_en: 'Rejected',
    tag_color: 'rose',
    sort_order: 3,
    is_active: true
  },
  {
    id: 16,
    category: 'via',
    type: 'rejected',
    content_vi: 'Sử dụng tài khoản vào các mục đích vi phạm pháp luật (cờ bạc, lừa đảo, spam độc hại)',
    content_en: 'Account used for illegal activities (gambling, scamming, harmful spam)',
    tag_vi: 'Vi phạm',
    tag_en: 'Violation',
    tag_color: 'rose',
    sort_order: 4,
    is_active: true
  }
];

const DEFAULT_FALLBACK_FAQS: FaqItem[] = [
  {
    id: 1,
    question_vi: 'Thời gian tiếp nhận và xử lý yêu cầu bảo hành là bao lâu?',
    question_en: 'How long does it take to process a warranty request?',
    answer_vi: 'Thời gian xử lý bảo hành thông thường từ 15 phút đến tối đa 24 giờ làm việc. Kỹ thuật viên sẽ kiểm tra tài khoản theo đúng khung giờ và điều kiện bảo hành.',
    answer_en: 'Standard warranty processing time ranges from 15 minutes up to 24 business hours. Technicians check accounts strictly based on conditions.',
    sort_order: 1,
    is_active: true,
    isOpen: false
  },
  {
    id: 2,
    question_vi: 'Làm thế nào để gửi yêu cầu bảo hành đơn hàng đã mua?',
    question_en: 'How can I submit a warranty claim for a purchased order?',
    answer_vi: 'Quý khách vui lòng truy cập trang "Lịch sử đơn hàng", bấm vào đơn hàng tương ứng và nhấn nút "Yêu cầu bảo hành" hoặc liên hệ trực tiếp với bộ phận Chăm sóc khách hàng qua Telegram/Zalo kèm Mã đơn hàng.',
    answer_en: 'Please visit "Order History", select your order and click "Request Warranty" or contact CS directly via Telegram/Zalo with your Order ID.',
    sort_order: 2,
    is_active: true,
    isOpen: false
  },
  {
    id: 3,
    question_vi: 'Trường hợp nào tài khoản sẽ bị từ chối bảo hành?',
    question_en: 'In what scenarios will a warranty claim be rejected?',
    answer_vi: 'Tài khoản bị từ chối bảo hành khi: Quá thời hạn bảo hành quy định, đã thay đổi thông tin (Email/Mật khẩu/2FA) đối với các loại tài khoản chưa được phép, sử dụng IP bẩn/bị đen, vi phạm chính sách của Facebook sau khi đã nhận bàn giao thành công.',
    answer_en: 'Warranty is rejected if: exceeded time limit, unauthorized info modification (Email/Password/2FA), dirty/blacklisted IP usage, or Facebook policy violations after delivery.',
    sort_order: 3,
    is_active: true,
    isOpen: false
  },
  {
    id: 4,
    question_vi: 'Chính sách bảo hành BM xác minh doanh nghiệp (Business Verification)?',
    question_en: 'What is the policy for Business Verified BMs?',
    answer_vi: 'BM xác minh doanh nghiệp được bảo hành 07 ngày đối với các lỗi liên quan đến trạng thái xác minh bị mất hoặc link gia nhập không hoạt động. Không bảo hành trường hợp BM chết do lên camp vi phạm chính sách hoặc add thẻ bẩn.',
    answer_en: 'Verified BMs have a 07-day warranty for lost verification status or broken join links. No warranty for dead BM caused by policy-violating ads or bad payment cards.',
    sort_order: 4,
    is_active: true,
    isOpen: false
  }
];

const DEFAULT_FALLBACK_CATEGORIES: PolicyCategoryItem[] = [
  { id: 1, key: 'bm', name_vi: 'Business Manager (BM)', name_en: 'Business Manager (BM)', icon: 'package', sort_order: 10, is_active: true },
  { id: 2, key: 'via', name_vi: 'Via / Clone Facebook', name_en: 'Via / Clone Facebook', icon: 'customers', sort_order: 20, is_active: true }
];

@Component({
  selector: 'app-faq',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    PageHeaderComponent,
    IconComponent,
    TranslatePipe
  ],
  templateUrl: './faq.component.html',
  styleUrls: ['./faq.component.scss']
})
export class FaqComponent implements OnInit {
  public readonly lang = inject(LanguageService);
  private readonly faqService = inject(FaqService);
  public readonly stateService = inject(StateService);

  public categories = signal<PolicyCategoryItem[]>(DEFAULT_FALLBACK_CATEGORIES);
  public activeTab = signal<string>('bm');
  public policyType = signal<'all' | 'accepted' | 'rejected'>('all');
  public isLoading = signal<boolean>(true);

  public policies = signal<WarrantyPolicyItem[]>(DEFAULT_FALLBACK_POLICIES);
  public faqItems = signal<FaqItem[]>(DEFAULT_FALLBACK_FAQS);

  public readonly activeCategories = computed(() => {
    return this.categories().filter(c => c.is_active);
  });

  public readonly currentCategoryName = computed(() => {
    const tabKey = this.activeTab();
    const cat = this.categories().find(c => c.key === tabKey);
    if (!cat) return tabKey.toUpperCase();
    return this.lang.currentLang() === 'en' ? cat.name_en : cat.name_vi;
  });

  public readonly acceptedPolicies = computed(() => {
    const tab = this.activeTab();
    return this.policies().filter(p => p.category === tab && p.type === 'accepted');
  });

  public readonly rejectedPolicies = computed(() => {
    const tab = this.activeTab();
    return this.policies().filter(p => p.category === tab && p.type === 'rejected');
  });

  public ngOnInit(): void {
    this.loadFaqs();
  }

  public loadFaqs(): void {
    this.isLoading.set(true);
    this.faqService.getPublicFaqs().subscribe({
      next: (res) => {
        if (res && res.success) {
          if (res.categories && res.categories.length > 0) {
            this.categories.set(res.categories);
            const activeList = res.categories.filter(c => c.is_active);
            if (activeList.length > 0 && !activeList.some(c => c.key === this.activeTab())) {
              this.activeTab.set(activeList[0].key);
            }
          }
          if (res.policies && res.policies.length > 0) {
            this.policies.set(res.policies);
          }
          if (res.faqs && res.faqs.length > 0) {
            this.faqItems.set(res.faqs.map(f => ({ ...f, isOpen: false })));
          }
        }
        this.isLoading.set(false);
      },
      error: () => {
        this.isLoading.set(false);
      }
    });
  }

  public setTab(tab: string): void {
    this.activeTab.set(tab);
  }

  public setPolicyType(type: 'all' | 'accepted' | 'rejected'): void {
    this.policyType.set(type);
  }

  public toggleFaq(id: number): void {
    this.faqItems.update(items =>
      items.map(item =>
        item.id === id ? { ...item, isOpen: !item.isOpen } : item
      )
    );
  }

  public getPolicyContent(policy: WarrantyPolicyItem): string {
    return this.lang.currentLang() === 'en' ? policy.content_en : policy.content_vi;
  }

  public getPolicySubContent(policy: WarrantyPolicyItem): string | null | undefined {
    return this.lang.currentLang() === 'en' ? policy.sub_content_en : policy.sub_content_vi;
  }

  public getPolicyTag(policy: WarrantyPolicyItem): string | null | undefined {
    return this.lang.currentLang() === 'en' ? policy.tag_en : policy.tag_vi;
  }

  public getFaqQuestion(faq: FaqItem): string {
    return this.lang.currentLang() === 'en' ? faq.question_en : faq.question_vi;
  }

  public getFaqAnswer(faq: FaqItem): string {
    return this.lang.currentLang() === 'en' ? faq.answer_en : faq.answer_vi;
  }

  public getTagBadgeClass(color?: string): string {
    switch (color) {
      case 'emerald':
        return 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300';
      case 'amber':
        return 'bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300';
      case 'rose':
        return 'bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300';
      case 'blue':
      default:
        return 'bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300';
    }
  }

  public getTagIconName(color?: string): string {
    switch (color) {
      case 'emerald':
        return 'calendar';
      case 'amber':
        return 'clock';
      case 'rose':
        return 'warning';
      case 'blue':
      default:
        return 'clock';
    }
  }
}
