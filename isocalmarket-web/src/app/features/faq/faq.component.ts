import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

interface FaqAccordionItem {
  id: string;
  questionKey: string;
  answerKey: string;
  isOpen?: boolean;
}

@Component({
  selector: 'app-faq',
  standalone: true,
  imports: [
    CommonModule,
    PageHeaderComponent,
    IconComponent,
    TranslatePipe
  ],
  templateUrl: './faq.component.html',
  styleUrls: ['./faq.component.scss']
})
export class FaqComponent {
  public readonly lang = inject(LanguageService);

  public activeTab = signal<'bm' | 'via'>('bm');
  public policyType = signal<'all' | 'accepted' | 'rejected'>('all');

  public faqItems = signal<FaqAccordionItem[]>([
    { id: '1', questionKey: 'faq.q1', answerKey: 'faq.a1', isOpen: false },
    { id: '2', questionKey: 'faq.q2', answerKey: 'faq.a2', isOpen: false },
    { id: '3', questionKey: 'faq.q3', answerKey: 'faq.a3', isOpen: false },
    { id: '4', questionKey: 'faq.q4', answerKey: 'faq.a4', isOpen: false }
  ]);

  public setTab(tab: 'bm' | 'via'): void {
    this.activeTab.set(tab);
  }

  public setPolicyType(type: 'all' | 'accepted' | 'rejected'): void {
    this.policyType.set(type);
  }

  public toggleFaq(id: string): void {
    this.faqItems.update(items =>
      items.map(item =>
        item.id === id ? { ...item, isOpen: !item.isOpen } : item
      )
    );
  }
}
