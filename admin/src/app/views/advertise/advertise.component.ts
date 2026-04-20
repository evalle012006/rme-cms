import {
  AfterViewInit,
  Component,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import { DataTableDirective } from 'angular-datatables';
import { Subject } from 'rxjs';
import { DatatableService, UserService } from '../../services';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { ActivatedRoute } from '@angular/router';
import { AdvertiseService } from '../../services/advertise.service';

@Component({
  templateUrl: 'advertise.component.html',
})
export class AdvertiseComponent implements OnInit, AfterViewInit, OnDestroy {
  advertises: any = [];
  modalRef: BsModalRef;
  currentMode = '';
  activeRow = null;

  @ViewChild(DataTableDirective)
  dtElement: DataTableDirective;
  dtOptions: DataTables.Settings = {};
  dtTrigger: Subject<any> = new Subject();

  constructor(
    private datatableService: DatatableService,
    private modalService: BsModalService,
    private _advertiseService: AdvertiseService,
    private _activatedRouter: ActivatedRoute
  ) {}

  ngOnInit() {
    this.getAllUsers();
    this.currentMode = this._activatedRouter.snapshot.url[0]['path'];
  }

  ngAfterViewInit(): void {
    this.dtTrigger.next();
  }

  ngOnDestroy(): void {
    this.dtTrigger.unsubscribe();
  }

  getAllUsers() {
    this.dtOptions = {
      pagingType: 'full_numbers',
      pageLength: 50,
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
              status: this.currentMode === 'approve' ? 1 : 0,
            },
            'datatable-advertises'
          )
          .subscribe((resp) => {
            this.advertises = resp.data;
            callback({
              recordsTotal: resp.recordsTotal,
              recordsFiltered: resp.recordsFiltered,
              data: [],
            });
          });
      },
      order: [],
      columns: [
        { name: 'title' },
        { name: 'company.company_name', orderable: false },
        { name: 'status', searchable: false, orderable: false },
        { name: 'change_action', searchable: false, orderable: false },
      ],
    };
  }

  rerender() {
    this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
      dtInstance.destroy();
      this.dtTrigger.next();
    });
  }

  openModal = (templateRef, advertise) => {
    this.modalRef = this.modalService.show(templateRef);
    this.activeRow = advertise;
  };

  hideModal = () => {
    this.modalRef.hide();
  };

  changeStatus = () => {
    const data = {
      id: this.activeRow.id,
      status: this.currentMode === 'approve' ? 0 : 1,
    };
    this.hideModal();
    this._advertiseService.changeAdvertiseStatus(data).subscribe(() => {
      this.rerender();
    });
  };
}
