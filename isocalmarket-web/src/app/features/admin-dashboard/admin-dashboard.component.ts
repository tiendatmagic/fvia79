import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '@core/services/api.service';
import { StateService } from '@core/services/state.service';
import { RouterModule } from '@angular/router';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';

@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    TooltipDirective,
  ],
  templateUrl: './admin-dashboard.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminDashboardComponent implements OnInit {
  private readonly apiService = inject(ApiService);
  private readonly stateService = inject(StateService);

  public readonly stats = signal<any>(null);
  public readonly loading = signal<boolean>(true);

  ngOnInit() {
    this.loadStats();
  }

  public loadStats() {
    this.loading.set(true);
    this.apiService.get<any>('v1/admin/dashboard-stats').subscribe({
      next: (res) => {
        this.stats.set(res);
        this.loading.set(false);
      },
      error: (err) => {
        this.stateService.showToast('Không thể tải số liệu thống kê!', 'error');
        this.loading.set(false);
      }
    });
  }
}
