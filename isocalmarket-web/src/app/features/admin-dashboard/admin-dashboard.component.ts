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
import { CustomDateTimeRangeComponent, DateTimeRangeValue } from '@shared/components/custom-date-time-range/custom-date-time-range.component';
import { FormsModule } from '@angular/forms';
import { HttpParams } from '@angular/common/http';

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
    FormsModule,
    CustomDateTimeRangeComponent
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
  public selectedDateRange: DateTimeRangeValue = { startDate: '', endDate: '' };

  ngOnInit() {
    this.loadStats();
  }

  public loadStats() {
    this.loading.set(true);
    let params = new HttpParams();
    if (this.selectedDateRange.startDate) {
      params = params.set('start_date', this.selectedDateRange.startDate);
    }
    if (this.selectedDateRange.endDate) {
      params = params.set('end_date', this.selectedDateRange.endDate);
    }

    this.apiService.get<any>('v1/admin/dashboard-stats', params).subscribe({
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
