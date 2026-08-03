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

const COUNTRY_RULES: { code: string; name: string; keywords?: RegExp }[] = [
  { code: 'us', name: 'United States', keywords: /\b(united\s*states|usa|mỹ|american)\b/i },
  { code: 'vn', name: 'Vietnam', keywords: /\b(vietnam|viet\s*nam|việt\s*nam|viet)\b/i },
  { code: 'th', name: 'Thailand', keywords: /\b(thailand|thai\s*lan|thái\s*lan|thái|thai)\b/i },
  { code: 'ph', name: 'Philippines', keywords: /\b(philippines|phillippines|philippine|phils|phil)\b/i },
  { code: 'id', name: 'Indonesia', keywords: /\b(indonesia|indo)\b/i },
  { code: 'gb', name: 'United Kingdom', keywords: /\b(united\s*kingdom|britain|england|scotland|wales)\b/i },
  { code: 'jp', name: 'Japan', keywords: /\b(japan|nhật\s*bản|nhat\s*ban|nhật|nhat)\b/i },
  { code: 'kr', name: 'South Korea', keywords: /\b(korea|south\s*korea|hàn\s*quốc|han\s*quoc|hàn|han)\b/i },
  { code: 'tw', name: 'Taiwan', keywords: /\b(taiwan|đài\s*loan|dai\s*loan)\b/i },
  { code: 'br', name: 'Brazil', keywords: /\b(brazil|brasil)\b/i },
  { code: 'in', name: 'India', keywords: /\b(india|ấn\s*độ|an\s*do)\b/i },
  { code: 'de', name: 'Germany', keywords: /\b(germany|đức|duc)\b/i },
  { code: 'fr', name: 'France', keywords: /\b(france|pháp|phap)\b/i },
  { code: 'es', name: 'Spain', keywords: /\b(spain|tây\s*ban\s*nha|tay\s*ban\s*nha)\b/i },
  { code: 'it', name: 'Italy', keywords: /\b(italy|italia|ý)\b/i },
  { code: 'ru', name: 'Russia', keywords: /\b(russia|nga)\b/i },
  { code: 'au', name: 'Australia', keywords: /\b(australia|úc|uc)\b/i },
  { code: 'ca', name: 'Canada', keywords: /\b(canada)\b/i },
  { code: 'kh', name: 'Cambodia', keywords: /\b(cambodia|campuchia)\b/i },
  { code: 'la', name: 'Laos', keywords: /\b(laos|lào|lao)\b/i },
  { code: 'mm', name: 'Myanmar', keywords: /\b(myanmar|burma)\b/i },
  { code: 'my', name: 'Malaysia', keywords: /\b(malaysia|malay)\b/i },
  { code: 'sg', name: 'Singapore', keywords: /\b(singapore|sing)\b/i },
  { code: 'ua', name: 'Ukraine', keywords: /\b(ukraine)\b/i },
  { code: 'pk', name: 'Pakistan', keywords: /\b(pakistan)\b/i },
  { code: 'bd', name: 'Bangladesh', keywords: /\b(bangladesh)\b/i },
  { code: 'nl', name: 'Netherlands', keywords: /\b(netherlands|hà\s*lan|ha\s*lan|holland)\b/i },
  { code: 'pl', name: 'Poland', keywords: /\b(poland|ba\s*lan)\b/i },
  { code: 'tr', name: 'Turkey', keywords: /\b(turkey|thổ\s*nhĩ\s*kỳ|tho\s*nhi\s*ky)\b/i },
  { code: 'sa', name: 'Saudi Arabia', keywords: /\b(saudi\s*arabia|saudi)\b/i },
  { code: 'ae', name: 'United Arab Emirates', keywords: /\b(uae|emirates|dubai)\b/i },
  { code: 'ar', name: 'Argentina', keywords: /\b(argentina)\b/i },
  { code: 'co', name: 'Colombia', keywords: /\b(colombia)\b/i },
  { code: 'pe', name: 'Peru', keywords: /\b(peru)\b/i },
  { code: 'cl', name: 'Chile', keywords: /\b(chile)\b/i },
  { code: 'bo', name: 'Bolivia', keywords: /\b(bolivia)\b/i },
  { code: 've', name: 'Venezuela', keywords: /\b(venezuela)\b/i },
  { code: 'ec', name: 'Ecuador', keywords: /\b(ecuador)\b/i },
  { code: 'uy', name: 'Uruguay', keywords: /\b(uruguay)\b/i },
  { code: 'py', name: 'Paraguay', keywords: /\b(paraguay)\b/i },
  { code: 'gt', name: 'Guatemala', keywords: /\b(guatemala)\b/i },
  { code: 'hn', name: 'Honduras', keywords: /\b(honduras)\b/i },
  { code: 'sv', name: 'El Salvador', keywords: /\b(el\s*salvador)\b/i },
  { code: 'ni', name: 'Nicaragua', keywords: /\b(nicaragua)\b/i },
  { code: 'cr', name: 'Costa Rica', keywords: /\b(costa\s*rica)\b/i },
  { code: 'pa', name: 'Panama', keywords: /\b(panama|panamá)\b/i },
  { code: 'mx', name: 'Mexico', keywords: /\b(mexico|mêxicô|me-xi-co)\b/i },
  { code: 'do', name: 'Dominican Republic', keywords: /\b(dominican)\b/i },
  { code: 'pr', name: 'Puerto Rico', keywords: /\b(puerto\s*rico)\b/i },
  { code: 'jm', name: 'Jamaica', keywords: /\b(jamaica)\b/i },
  { code: 'cu', name: 'Cuba', keywords: /\b(cuba)\b/i },
  { code: 'cn', name: 'China', keywords: /\b(china|trung\s*quốc|trung\s*quoc|trung)\b/i },
  { code: 'hk', name: 'Hong Kong', keywords: /\b(hong\s*kong)\b/i },
  { code: 'mo', name: 'Macao', keywords: /\b(macao|macau)\b/i },
  { code: 'nz', name: 'New Zealand', keywords: /\b(new\s*zealand)\b/i },
  { code: 'tl', name: 'Timor-Leste', keywords: /\b(timor\s*leste|timor|east\s*timor)\b/i },
  { code: 'eg', name: 'Egypt', keywords: /\b(egypt|ai\s*cập|ai\s*cap)\b/i },
  { code: 'ng', name: 'Nigeria', keywords: /\b(nigeria)\b/i },
  { code: 'cg', name: 'Congo', keywords: /\b(congo)\b/i },
  { code: 'za', name: 'South Africa', keywords: /\b(south\s*africa|nam\s*phi)\b/i },
  { code: 'ke', name: 'Kenya', keywords: /\b(kenya)\b/i },
  { code: 'gh', name: 'Ghana', keywords: /\b(ghana)\b/i },
  { code: 'ma', name: 'Morocco', keywords: /\b(morocco|ma\s*rốc|ma\s*roc)\b/i },
  { code: 'dz', name: 'Algeria', keywords: /\b(algeria)\b/i },
  { code: 'tn', name: 'Tunisia', keywords: /\b(tunisia)\b/i },
  { code: 'se', name: 'Sweden', keywords: /\b(sweden|thụy\s*điển|thuy\s*dien)\b/i },
  { code: 'no', name: 'Norway', keywords: /\b(norway|na\s*uy|na\s*ui)\b/i },
  { code: 'dk', name: 'Denmark', keywords: /\b(denmark|đan\s*mạch|dan\s*mach)\b/i },
  { code: 'fi', name: 'Finland', keywords: /\b(finland|phần\s*lan|phan\s*lan)\b/i },
  { code: 'is', name: 'Iceland', keywords: /\b(iceland)\b/i },
  { code: 'pt', name: 'Portugal', keywords: /\b(portugal|bồ\s*đào\s*nha|bo\s*dao\s*nha)\b/i },
  { code: 'gr', name: 'Greece', keywords: /\b(greece|hy\s*lạp|hy\s*lap)\b/i },
  { code: 'ro', name: 'Romania', keywords: /\b(romania)\b/i },
  { code: 'bg', name: 'Bulgaria', keywords: /\b(bulgaria)\b/i },
  { code: 'cz', name: 'Czechia', keywords: /\b(czech|séc|sec)\b/i },
  { code: 'sk', name: 'Slovakia', keywords: /\b(slovakia)\b/i },
  { code: 'hu', name: 'Hungary', keywords: /\b(hungary)\b/i },
  { code: 'at', name: 'Austria', keywords: /\b(austria|áo|ao)\b/i },
  { code: 'ch', name: 'Switzerland', keywords: /\b(switzerland|thụy\s*sĩ|thuy\s*si)\b/i },
  { code: 'be', name: 'Belgium', keywords: /\b(belgium|bỉ|bi)\b/i },
  { code: 'ie', name: 'Ireland', keywords: /\b(ireland)\b/i },
  { code: 'lt', name: 'Lithuania', keywords: /\b(lithuania)\b/i },
  { code: 'lv', name: 'Latvia', keywords: /\b(latvia)\b/i },
  { code: 'ee', name: 'Estonia', keywords: /\b(estonia)\b/i },
  { code: 'by', name: 'Belarus', keywords: /\b(belarus)\b/i },
  { code: 'ge', name: 'Georgia', keywords: /\b(georgia)\b/i },
  { code: 'am', name: 'Armenia', keywords: /\b(armenia)\b/i },
  { code: 'az', name: 'Azerbaijan', keywords: /\b(azerbaijan)\b/i },
  { code: 'kz', name: 'Kazakhstan', keywords: /\b(kazakhstan)\b/i },
  { code: 'uz', name: 'Uzbekistan', keywords: /\b(uzbekistan)\b/i },
  { code: 'kg', name: 'Kyrgyzstan', keywords: /\b(kyrgyzstan)\b/i },
  { code: 'tj', name: 'Tajikistan', keywords: /\b(tajikistan)\b/i },
  { code: 'tm', name: 'Turkmenistan', keywords: /\b(turkmenistan)\b/i },
  { code: 'np', name: 'Nepal', keywords: /\b(nepal)\b/i },
  { code: 'lk', name: 'Sri Lanka', keywords: /\b(sri\s*lanka)\b/i },
  { code: 'mn', name: 'Mongolia', keywords: /\b(mongolia|mông\s*cổ|mong\s*co)\b/i },
  { code: 'iq', name: 'Iraq', keywords: /\b(iraq)\b/i },
  { code: 'ir', name: 'Iran', keywords: /\b(iran)\b/i },
  { code: 'il', name: 'Israel', keywords: /\b(israel)\b/i },
  { code: 'jo', name: 'Jordan', keywords: /\b(jordan)\b/i },
  { code: 'lb', name: 'Lebanon', keywords: /\b(lebanon)\b/i },
  { code: 'kw', name: 'Kuwait', keywords: /\b(kuwait)\b/i },
  { code: 'qa', name: 'Qatar', keywords: /\b(qatar)\b/i },
  { code: 'bh', name: 'Bahrain', keywords: /\b(bahrain)\b/i },
  { code: 'om', name: 'Oman', keywords: /\b(oman)\b/i },
  { code: 'lu', name: 'Luxembourg', keywords: /\b(luxembourg)\b/i },
  { code: 'mt', name: 'Malta', keywords: /\b(malta)\b/i },
  { code: 'cy', name: 'Cyprus', keywords: /\b(cyprus)\b/i },
  { code: 'al', name: 'Albania', keywords: /\b(albania)\b/i },
  { code: 'ba', name: 'Bosnia', keywords: /\b(bosnia)\b/i },
  { code: 'md', name: 'Moldova', keywords: /\b(moldova)\b/i },
  { code: 'et', name: 'Ethiopia', keywords: /\b(ethiopia)\b/i },
  { code: 'tz', name: 'Tanzania', keywords: /\b(tanzania)\b/i },
  { code: 'ug', name: 'Uganda', keywords: /\b(uganda)\b/i },
  { code: 'cm', name: 'Cameroon', keywords: /\b(cameroon)\b/i },
  { code: 'ci', name: 'Ivory Coast', keywords: /\b(ivory\s*coast|cote\s*d\s*ivoire)\b/i },
  { code: 'sn', name: 'Senegal', keywords: /\b(senegal)\b/i },
  { code: 'zw', name: 'Zimbabwe', keywords: /\b(zimbabwe)\b/i },
  { code: 'zm', name: 'Zambia', keywords: /\b(zambia)\b/i },
  { code: 'ao', name: 'Angola', keywords: /\b(angola)\b/i },
  { code: 'mz', name: 'Mozambique', keywords: /\b(mozambique)\b/i },
  { code: 'sd', name: 'Sudan', keywords: /\b(sudan)\b/i },
  { code: 'ly', name: 'Libya', keywords: /\b(libya)\b/i },
  { code: 'hr', name: 'Croatia', keywords: /\b(croatia)\b/i },
  { code: 'rs', name: 'Serbia', keywords: /\b(serbia)\b/i },
  { code: 'si', name: 'Slovenia', keywords: /\b(slovenia)\b/i }
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
  @Input() disableFlag: boolean = false;

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
    if (changes['productName'] || changes['productId'] || changes['disableFlag']) {
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
    if (!title || this.disableFlag) return null;

    const nonCountryRegex = /\b(bm\d*|muabm\d*|clone\s*ig|hotmail|account\s*fb)\b/i;
    if (nonCountryRegex.test(title)) {
      return null;
    }

    const bracketMatch = title.match(/(?:\[|\()([a-zA-Z]{2})(?:\]|\))/);
    if (bracketMatch) {
      const code = bracketMatch[1].toLowerCase();
      const rule = COUNTRY_RULES.find(r => r.code === code);
      if (rule) return { code: rule.code, name: rule.name };
      return { code, name: code.toUpperCase() };
    }

    const normalized = this.removeAccents(title.toLowerCase());
    for (const rule of COUNTRY_RULES) {
      if (rule.keywords && (rule.keywords.test(title) || rule.keywords.test(normalized))) {
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
