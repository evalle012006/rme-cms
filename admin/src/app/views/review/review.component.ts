import {
  AfterViewInit,
  Component,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {Subject} from 'rxjs';

import {BsModalService, BsModalRef} from 'ngx-bootstrap/modal';
import {DataTableDirective} from 'angular-datatables';
import {DatatableService, LoaderService, OptionService, ReviewService} from '../../services';
import {ActivatedRoute} from '@angular/router';

@Component({
  templateUrl: 'review.component.html',
  styleUrls: ['review.styles.scss'],
})
export class ReviewComponent implements OnInit, AfterViewInit, OnDestroy {
  reviews: any = [];
  activeRow: any = {};
  filter: string = '';
  modalRef: BsModalRef;
  currentMode = ''; // approve or panding

  @ViewChild(DataTableDirective)
  dtElement: DataTableDirective;
  dtOptions: DataTables.Settings = {};
  dtTrigger: Subject<any> = new Subject();

  constructor(
    private datatableService: DatatableService,
    private _reviewService: ReviewService,
    private modalService: BsModalService,
    private _activatedRouter: ActivatedRoute
  ) {
  }

  ngOnInit() {
    this.getReviews();
    this.currentMode = this._activatedRouter.snapshot.url[0]['path'];
  }

  ngAfterViewInit(): void {
    this.dtTrigger.next();
  }

  ngOnDestroy(): void {
    this.dtTrigger.unsubscribe();
  }

  getReviews() {
    this.dtOptions = {
      pagingType: 'full_numbers',
      pageLength: 10,
      lengthMenu: [5, 10, 25, 50],
      serverSide: true,
      searchDelay: 1000,
      processing: true,
      language: {
        searchPlaceholder: 'Search...',
        search: '',
      },
      ajax: (dataTablesParameters: any, callback) => {
        this.datatableService
          .getTableData(
            {
              ...dataTablesParameters,
              filter: this.filter,
              status: this.currentMode === 'approve' ? 1 : 0,
            },
            'datatable-reviews'
          )
          .subscribe((resp) => {
            this.reviews = resp.data;
            callback({
              recordsTotal: resp.recordsTotal,
              recordsFiltered: resp.recordsFiltered,
              data: [],
            });
          });
      },
      order: [],
      columns: [
        {name: 'title'},
        {name: 'status', orderable: false, searchable: false},
        {name: 'recommendation', orderable: false, searchable: false},
        {name: '', orderable: false, searchable: false},
        {name: 'action', orderable: false, searchable: false},
      ],
    };
  }

  rerender() {
    this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
      dtInstance.destroy();
      this.dtTrigger.next();
    });
  }

  onSelectReview(value) {
    this.filter = value;
    this.rerender();
  }

  showModal = (row, templefRef) => {
    this.modalRef = this.modalService.show(templefRef);
    this.activeRow = row;
  };

  changeStatus = () => {
    const newStatus = +this.activeRow.status === 1 ? 0 : 1;
    const postData = {
      status: newStatus,
      id: this.activeRow.id,
    };
    this.modalRef.hide();
    this._reviewService.changeStatus(postData).subscribe((res) => {
      this.rerender();
    });
  };

  hideModal = () => {
    this.modalRef.hide();
  };

  deleteReview() {
    if (this.activeRow) {
      LoaderService.show();
      this._reviewService.deleteReview(this.activeRow.id).subscribe((response) => {
        this.rerender();
      }, () => {
        // nothing
      }, () => {
        LoaderService.hide();
        this.modalRef.hide();
      });
    } else {
      this.modalRef.hide();
    }
  }
}
