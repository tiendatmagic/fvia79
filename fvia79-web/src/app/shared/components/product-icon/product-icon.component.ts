import { Component, Input, OnChanges, SimpleChanges, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';

export interface CountryMatch {
  code: string;
  name: string;
}

export interface GenericIconSpec {
  id: string;
  bgGradient: string;
  textColor: string;
  borderColor: string;
}

const COUNTRY_RULES: { code: string; name: string; pattern: RegExp }[] = [
  // Multi-word / Specific names first
  { code: 'us', name: 'United States', pattern: /\b(united\s*states|usa|mỹ|my|american|\bus\b)\b/i },
  { code: 'id', name: 'Indonesia', pattern: /\b(indonesia|indo|\bid\b)\b/i },
  { code: 'ph', name: 'Philippines', pattern: /\b(philippines|phillippines|philippine|phils|phil|\bph\b)\b/i },
  { code: 'cd', name: 'Congo', pattern: /\b(congo|\bcd\b)\b/i },
  { code: 'vn', name: 'Vietnam', pattern: /\b(vietnam|viet\s*nam|việt\s*nam|\bvn\b)\b/i },
  { code: 'th', name: 'Thailand', pattern: /\b(thailand|thai\s*lan|thái\s*lan|thai|thái|\bth\b)\b/i },
  { code: 'tl', name: 'Timor-Leste', pattern: /\b(timor\s*leste|east\s*timor|đông\s*timor|dong\s*timor|timor|\btl\b)\b/i },
  { code: 'ec', name: 'Ecuador', pattern: /\b(ecuador|\bec\b)\b/i },
  { code: 'br', name: 'Brazil', pattern: /\b(brazil|brasil|\bbr\b)\b/i },
  { code: 'in', name: 'India', pattern: /\b(india|ấn\s*độ|an\s*do|\bin\b)\b/i },
  { code: 'mx', name: 'Mexico', pattern: /\b(mexico|méxico|\bmx\b)\b/i },
  { code: 'de', name: 'Germany', pattern: /\b(germany|đức|duc|\bde\b)\b/i },
  { code: 'fr', name: 'France', pattern: /\b(france|pháp|phap|\bfr\b)\b/i },
  { code: 'gb', name: 'United Kingdom', pattern: /\b(united\s*kingdom|uk|britain|england|anh|\bgb\b)\b/i },
  { code: 'es', name: 'Spain', pattern: /\b(spain|tây\s*ban\s*nha|tay\s*ban\s*nha|\bes\b)\b/i },
  { code: 'it', name: 'Italy', pattern: /\b(italy|italia|ý|y|\bit\b)\b/i },
  { code: 'jp', name: 'Japan', pattern: /\b(japan|nhật\s*bản|nhat\s*ban|nhật|nhat|\bjp\b)\b/i },
  { code: 'kr', name: 'South Korea', pattern: /\b(korea|south\s*korea|hàn\s*quốc|han\s*quoc|hàn|han|\bkr\b)\b/i },
  { code: 'tw', name: 'Taiwan', pattern: /\b(taiwan|đài\s*loan|dai\s*loan|\btw\b)\b/i },
  { code: 'ru', name: 'Russia', pattern: /\b(russia|nga|\bru\b)\b/i },
  { code: 'au', name: 'Australia', pattern: /\b(australia|úc|\bau\b)\b/i },
  { code: 'ca', name: 'Canada', pattern: /\b(canada|\bca\b)\b/i },
  { code: 'kh', name: 'Cambodia', pattern: /\b(cambodia|campuchia|\bkh\b)\b/i },
  { code: 'la', name: 'Laos', pattern: /\b(laos|lào|\bla\b)\b/i },
  { code: 'mm', name: 'Myanmar', pattern: /\b(myanmar|burma|\bmm\b)\b/i },
  { code: 'my', name: 'Malaysia', pattern: /\b(malaysia|malay|\bmy\b)\b/i },
  { code: 'sg', name: 'Singapore', pattern: /\b(singapore|sing|\bsg\b)\b/i },
  { code: 'ua', name: 'Ukraine', pattern: /\b(ukraine|\bua\b)\b/i },
  { code: 'pk', name: 'Pakistan', pattern: /\b(pakistan|\bpk\b)\b/i },
  { code: 'bd', name: 'Bangladesh', pattern: /\b(bangladesh|\bbd\b)\b/i },
  { code: 'nl', name: 'Netherlands', pattern: /\b(netherlands|hà\s*lan|holland|\bnl\b)\b/i },
  { code: 'pl', name: 'Poland', pattern: /\b(poland|ba\s*lan|\bpl\b)\b/i },
  { code: 'tr', name: 'Turkey', pattern: /\b(turkey|thổ\s*nhĩ\s*kỳ|\btr\b)\b/i },
  { code: 'sa', name: 'Saudi Arabia', pattern: /\b(saudi\s*arabia|saudi|\bsa\b)\b/i },
  { code: 'ae', name: 'United Arab Emirates', pattern: /\b(uae|emirates|dubai|\bae\b)\b/i },
  { code: 'ar', name: 'Argentina', pattern: /\b(argentina|\bar\b)\b/i },
  { code: 'co', name: 'Colombia', pattern: /\b(colombia|\bco\b)\b/i },
  { code: 'pe', name: 'Peru', pattern: /\b(peru|\bpe\b)\b/i },
  { code: 'cl', name: 'Chile', pattern: /\b(chile|\bcl\b)\b/i },
  { code: 'bo', name: 'Bolivia', pattern: /\b(bolivia|\bbo\b)\b/i },
  { code: 've', name: 'Venezuela', pattern: /\b(venezuela|\bve\b)\b/i },
  { code: 'uy', name: 'Uruguay', pattern: /\b(uruguay|\buy\b)\b/i },
  { code: 'py', name: 'Paraguay', pattern: /\b(paraguay|\bpy\b)\b/i },
  { code: 'gt', name: 'Guatemala', pattern: /\b(guatemala|\bgt\b)\b/i },
  { code: 'hn', name: 'Honduras', pattern: /\b(honduras|\bhn\b)\b/i },
  { code: 'sv', name: 'El Salvador', pattern: /\b(el\s*salvador|\bsv\b)\b/i },
  { code: 'ni', name: 'Nicaragua', pattern: /\b(nicaragua|\bni\b)\b/i },
  { code: 'cr', name: 'Costa Rica', pattern: /\b(costa\s*rica|\bcr\b)\b/i },
  { code: 'pa', name: 'Panama', pattern: /\b(panama|panamá|\bpa\b)\b/i },
  { code: 'do', name: 'Dominican Republic', pattern: /\b(dominican|\bdo\b)\b/i },
  { code: 'pr', name: 'Puerto Rico', pattern: /\b(puerto\s*rico|\bpr\b)\b/i },
  { code: 'jm', name: 'Jamaica', pattern: /\b(jamaica|\bjm\b)\b/i },
  { code: 'cu', name: 'Cuba', pattern: /\b(cuba|\bcu\b)\b/i },
  { code: 'cn', name: 'China', pattern: /\b(china|trung\s*quốc|\bcn\b)\b/i },
  { code: 'hk', name: 'Hong Kong', pattern: /\b(hong\s*kong|\bhk\b)\b/i },
  { code: 'mo', name: 'Macao', pattern: /\b(macao|macau|\bmo\b)\b/i },
  { code: 'nz', name: 'New Zealand', pattern: /\b(new\s*zealand|\bnz\b)\b/i },
  { code: 'eg', name: 'Egypt', pattern: /\b(egypt|ai\s*cập|\beg\b)\b/i },
  { code: 'ng', name: 'Nigeria', pattern: /\b(nigeria|\bng\b)\b/i },
  { code: 'za', name: 'South Africa', pattern: /\b(south\s*africa|nam\s*phi|\bza\b)\b/i },
  { code: 'ke', name: 'Kenya', pattern: /\b(kenya|\bke\b)\b/i },
  { code: 'gh', name: 'Ghana', pattern: /\b(ghana|\bgh\b)\b/i },
  { code: 'ma', name: 'Morocco', pattern: /\b(morocco|ma\s*rốc|\bma\b)\b/i },
  { code: 'dz', name: 'Algeria', pattern: /\b(algeria|\bdz\b)\b/i },
  { code: 'tn', name: 'Tunisia', pattern: /\b(tunisia|\btn\b)\b/i },
  { code: 'se', name: 'Sweden', pattern: /\b(sweden|thụy\s*điển|\bse\b)\b/i },
  { code: 'no', name: 'Norway', pattern: /\b(norway|na\s*uy|\bno\b)\b/i },
  { code: 'dk', name: 'Denmark', pattern: /\b(denmark|đan\s*mạch|\bdk\b)\b/i },
  { code: 'fi', name: 'Finland', pattern: /\b(finland|phần\s*lan|\bfi\b)\b/i },
  { code: 'is', name: 'Iceland', pattern: /\b(iceland|\bis\b)\b/i },
  { code: 'pt', name: 'Portugal', pattern: /\b(portugal|bồ\s*đào\s*nha|\bpt\b)\b/i },
  { code: 'gr', name: 'Greece', pattern: /\b(greece|hy\s*lạp|\bgr\b)\b/i },
  { code: 'ro', name: 'Romania', pattern: /\b(romania|\bro\b)\b/i },
  { code: 'bg', name: 'Bulgaria', pattern: /\b(bulgaria|\bbg\b)\b/i },
  { code: 'cz', name: 'Czechia', pattern: /\b(czech|séc|\bcz\b)\b/i },
  { code: 'sk', name: 'Slovakia', pattern: /\b(slovakia|\bsk\b)\b/i },
  { code: 'hu', name: 'Hungary', pattern: /\b(hungary|\bhu\b)\b/i },
  { code: 'at', name: 'Austria', pattern: /\b(austria|áo|\bat\b)\b/i },
  { code: 'ch', name: 'Switzerland', pattern: /\b(switzerland|thụy\s*sĩ|\bch\b)\b/i },
  { code: 'be', name: 'Belgium', pattern: /\b(belgium|bỉ|\bbe\b)\b/i },
  { code: 'ie', name: 'Ireland', pattern: /\b(ireland|\bie\b)\b/i },
  { code: 'lt', name: 'Lithuania', pattern: /\b(lithuania|\blt\b)\b/i },
  { code: 'lv', name: 'Latvia', pattern: /\b(latvia|\blv\b)\b/i },
  { code: 'ee', name: 'Estonia', pattern: /\b(estonia|\bee\b)\b/i },
  { code: 'by', name: 'Belarus', pattern: /\b(belarus|\bby\b)\b/i },
  { code: 'ge', name: 'Georgia', pattern: /\b(georgia|\bge\b)\b/i },
  { code: 'am', name: 'Armenia', pattern: /\b(armenia|\bam\b)\b/i },
  { code: 'az', name: 'Azerbaijan', pattern: /\b(azerbaijan|\baz\b)\b/i },
  { code: 'kz', name: 'Kazakhstan', pattern: /\b(kazakhstan|\bkz\b)\b/i },
  { code: 'uz', name: 'Uzbekistan', pattern: /\b(uzbekistan|\buz\b)\b/i },
  { code: 'hr', name: 'Croatia', pattern: /\b(croatia|\bhr\b)\b/i },
  { code: 'rs', name: 'Serbia', pattern: /\b(serbia|\brs\b)\b/i },
  { code: 'si', name: 'Slovenia', pattern: /\b(slovenia|\bsi\b)\b/i }
];

const GENERIC_ICONS: GenericIconSpec[] = [
  { id: 'globe', bgGradient: 'from-blue-500/20 to-cyan-500/20', textColor: 'text-blue-500 dark:text-blue-400', borderColor: 'border-blue-500/30' },
  { id: 'shield', bgGradient: 'from-purple-500/20 to-pink-500/20', textColor: 'text-purple-500 dark:text-purple-400', borderColor: 'border-purple-500/30' },
  { id: 'sparkles', bgGradient: 'from-amber-500/20 to-orange-500/20', textColor: 'text-amber-500 dark:text-amber-400', borderColor: 'border-amber-500/30' },
  { id: 'lightning', bgGradient: 'from-yellow-500/20 to-amber-500/20', textColor: 'text-amber-600 dark:text-yellow-400', borderColor: 'border-yellow-500/30' },
  { id: 'box', bgGradient: 'from-emerald-500/20 to-teal-500/20', textColor: 'text-emerald-500 dark:text-emerald-400', borderColor: 'border-emerald-500/30' },
  { id: 'key', bgGradient: 'from-indigo-500/20 to-violet-500/20', textColor: 'text-indigo-500 dark:text-indigo-400', borderColor: 'border-indigo-500/30' },
  { id: 'fire', bgGradient: 'from-red-500/20 to-rose-500/20', textColor: 'text-rose-500 dark:text-rose-400', borderColor: 'border-rose-500/30' },
  { id: 'diamond', bgGradient: 'from-sky-500/20 to-blue-500/20', textColor: 'text-sky-500 dark:text-sky-400', borderColor: 'border-sky-500/30' },
  { id: 'rocket', bgGradient: 'from-fuchsia-500/20 to-pink-500/20', textColor: 'text-fuchsia-500 dark:text-fuchsia-400', borderColor: 'border-fuchsia-500/30' },
  { id: 'crown', bgGradient: 'from-amber-400/20 to-yellow-600/20', textColor: 'text-amber-500 dark:text-amber-400', borderColor: 'border-amber-400/30' }
];

@Component({
  selector: 'app-product-icon',
  standalone: true,
  imports: [CommonModule],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './product-icon.component.html',
  host: {
    class: 'inline-flex items-center justify-center shrink-0'
  }
})
export class ProductIconComponent implements OnChanges {
  @Input() productName: string = '';
  @Input() productId: string | number = '';
  @Input() size: 'sm' | 'md' | 'lg' = 'md';

  public countryMatch: CountryMatch | null = null;
  public genericIcon: GenericIconSpec = GENERIC_ICONS[0];
  public imageError: boolean = false;

  public get flagUrl(): string {
    if (!this.countryMatch) return '';
    return `https://flagcdn.com/${this.countryMatch.code}.svg`;
  }

  public onImageError(): void {
    this.imageError = true;
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['productName'] || changes['productId']) {
      this.imageError = false;
      this.evaluateIcon();
    }
  }

  private evaluateIcon(): void {
    const match = this.findCountryMatch(this.productName || '');
    if (match) {
      this.countryMatch = match;
    } else {
      this.countryMatch = null;
      const seed = `${this.productName || ''}_${this.productId || ''}`;
      const index = this.hashString(seed) % GENERIC_ICONS.length;
      this.genericIcon = GENERIC_ICONS[index];
    }
  }

  private findCountryMatch(title: string): CountryMatch | null {
    if (!title) return null;
    const normalized = this.removeAccents(title.toLowerCase());

    for (const rule of COUNTRY_RULES) {
      if (rule.pattern.test(title) || rule.pattern.test(normalized)) {
        return { code: rule.code, name: rule.name };
      }
    }
    return null;
  }

  private removeAccents(str: string): string {
    return str
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd')
      .replace(/Đ/g, 'D');
  }

  private hashString(str: string): number {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      hash = (hash << 5) - hash + str.charCodeAt(i);
      hash |= 0;
    }
    return Math.abs(hash);
  }

  public get containerClasses(): string {
    if (this.countryMatch && !this.imageError) {
      switch (this.size) {
        case 'sm':
          return 'w-6 h-4 rounded-xs text-xs';
        case 'lg':
          return 'w-10 h-6.5 rounded-sm text-base';
        case 'md':
        default:
          return 'w-8 h-5.5 rounded-xs text-sm';
      }
    }

    switch (this.size) {
      case 'sm':
        return 'w-6 h-6 rounded-md text-xs';
      case 'lg':
        return 'w-10 h-10 rounded-xl text-base';
      case 'md':
      default:
        return 'w-8 h-8 rounded-lg text-sm';
    }
  }

  public get iconClasses(): string {
    switch (this.size) {
      case 'sm':
        return 'w-3.5 h-3.5';
      case 'lg':
        return 'w-5 h-5';
      case 'md':
      default:
        return 'w-4 h-4';
    }
  }
}
