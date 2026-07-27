import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalRef } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-category-reorder-modal',
  standalone: true,
  imports: [
    CommonModule,
    ButtonComponent,
    IconComponent,
    TranslatePipe
  ],
  templateUrl: './category-reorder-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class CategoryReorderModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);

  public readonly categoriesList = signal<any[]>([]);
  public readonly isLoading = signal<boolean>(true);
  public readonly isSubmitting = signal<boolean>(false);

  ngOnInit() {
    this.loadCategories();
  }

  public loadCategories() {
    this.isLoading.set(true);
    this.state.productStore.fetchCategories().subscribe({
      next: (cats) => {
        const sorted = [...(cats || [])].sort((a, b) => {
          const o1 = a.sort_order ?? 0;
          const o2 = b.sort_order ?? 0;
          if (o1 !== o2) return o1 - o2;
          return (a.name || '').localeCompare(b.name || '', 'vi');
        });
        this.categoriesList.set(sorted);
        this.isLoading.set(false);
      },
      error: () => {
        this.isLoading.set(false);
      }
    });
  }

  public moveUp(index: number) {
    if (index <= 0) return;
    const list = [...this.categoriesList()];
    const temp = list[index];
    list[index] = list[index - 1];
    list[index - 1] = temp;
    this.categoriesList.set(list);
  }

  public moveDown(index: number) {
    const list = [...this.categoriesList()];
    if (index >= list.length - 1) return;
    const temp = list[index];
    list[index] = list[index + 1];
    list[index + 1] = temp;
    this.categoriesList.set(list);
  }

  public moveTop(index: number) {
    if (index <= 0) return;
    const list = [...this.categoriesList()];
    const [item] = list.splice(index, 1);
    list.unshift(item);
    this.categoriesList.set(list);
  }

  public moveBottom(index: number) {
    const list = [...this.categoriesList()];
    if (index >= list.length - 1) return;
    const [item] = list.splice(index, 1);
    list.push(item);
    this.categoriesList.set(list);
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onSave() {
    if (this.isSubmitting()) return;
    const payload = this.categoriesList().map((item, idx) => ({
      id: item.id,
      sort_order: idx
    }));

    this.isSubmitting.set(true);
    this.state.productStore.reorderCategories(payload).pipe(
      finalize(() => this.isSubmitting.set(false))
    ).subscribe((res) => {
      if (res !== null) {
        this.modalRef.close(true);
      }
    });
  }
}
