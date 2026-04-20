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
import { ToastrService } from 'ngx-toastr';

@Component({
  templateUrl: 'user.component.html',
  styleUrls: ['user.component.scss'],
})
export class UserComponent implements OnInit, AfterViewInit, OnDestroy {
  users: any = [];
  modalRef: BsModalRef;

  activePerson: any = {};

  @ViewChild(DataTableDirective)
  dtElement: DataTableDirective;
  dtOptions: DataTables.Settings = {};
  dtTrigger: Subject<any> = new Subject();

  constructor(
    private datatableService: DatatableService,
    private modalService: BsModalService,
    private _userService: UserService,
    private _toasterService: ToastrService
  ) {}

  ngOnInit() {
    this.getAllUsers();
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
          .getTableData(dataTablesParameters, 'datatable-users')
          .subscribe((resp) => {
            this.users = resp.data;
            callback({
              recordsTotal: resp.recordsTotal,
              recordsFiltered: resp.recordsFiltered,
              data: [],
            });
          });
      },
      order: [],
      columns: [
        { name: 'id', orderable: false },
        { name: 'email' },
        { name: 'change_request', searchable: false, orderable: false },
        { name: 'action', searchable: false, orderable: false },
      ],
    };
  }

  rerender() {
    this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
      dtInstance.destroy();
      this.dtTrigger.next();
    });
  }

  openModal = (templateRef, personObj) => {
    this.modalRef = this.modalService.show(templateRef);
    this.activePerson = { ...personObj };
  };
  hideModal = () => {
    this.modalRef.hide();
  };
  changeRequestStatus = (status) => {
    const postData = {
      id: this.activePerson.id,
      display_name: this.activePerson.requested_display_name,
      status,
    };
    this.hideModal();
    this._userService.changeRequestStatus(postData).subscribe(
      (res: any) => {
        this._toasterService.success(res.message);
        this.rerender();
      },
      (error) => {
        this.hideModal();
        this._toasterService.error(error.message);
      }
    );
  };
}
