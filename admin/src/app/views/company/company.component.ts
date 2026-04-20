import {
  AfterViewInit,
  Component,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {
  DatatableService,
  CompanyService,
  LoaderService,
} from '../../services';
import { DataTableDirective } from 'angular-datatables';
import { ActivatedRoute, Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { FormBuilder } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { Subject } from 'rxjs';

@Component({
  templateUrl: 'company.component.html',
})
export class CompanyComponent implements AfterViewInit, OnDestroy, OnInit {
  @ViewChild(DataTableDirective) public dtElement: DataTableDirective;
  public dtOptions: DataTables.Settings = {};
  public dtTrigger: Subject<any> = new Subject();
  public companies = [];
  modalRef: BsModalRef;
  activeId = 0;

  currentMode = ''; // approve or panding

  constructor(
    private _datatableService: DatatableService,
    private _router: Router,
    private toaster: ToastrService,
    private modalService: BsModalService,
    private _companyService: CompanyService,
    private _formBuilder: FormBuilder,
    private _activatedRouter: ActivatedRoute
  ) {}

  ngOnInit() {
    this.getCompanyDetails();
    this.currentMode = this._activatedRouter.snapshot.url[0]['path'];
  }

  ngAfterViewInit(): void {
    this.dtTrigger.next();
  }

  ngOnDestroy(): void {
    this.dtTrigger.unsubscribe();
  }

  rerender(): void {
    this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
      dtInstance.destroy();
      this.dtTrigger.next();
    });
  }

  getCompanyDetails() {
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
        this._datatableService
          .getTableData(
            {
              ...dataTablesParameters,
              status: this.currentMode === 'approve' ? 1 : 0,
            },
            'datatable-company-details'
          )
          .subscribe((resp) => {
            this.companies = resp.data;
            // this.setMarkers();
            callback({
              recordsTotal: resp.recordsTotal,
              recordsFiltered: resp.recordsFiltered,
              data: [],
            });
          });
      },
      order: [],
      columns: [
        { name: 'company_name' },
        { name: 'id', orderable: false },
        { name: 'id', orderable: false },
      ],
    };
  }

  redirectToEditCompany(comId) {
    this._router.navigate(['/', 'company', 'edit', comId]);
  }

  setServeErrors(errorResponse) {
    const arr: any = [];
    for (const controlKey of errorResponse.errors) {
      arr.push(controlKey);
    }
    this.toaster.error('error', arr);
  }

  showModal = (id, templefRef) => {
    this.modalRef = this.modalService.show(templefRef);
    this.activeId = +id;
  };

  changeStatus = () => {
    const data = {
      id: this.activeId,
      status: this.currentMode === 'approve' ? 0 : 1,
    };
    this.modalRef.hide();
    this._companyService.changeCompanyStatus(data).subscribe((res) => {
      this.rerender();
    });
  };

  hideModal = () => {
    this.modalRef.hide();
  };

  deleteCompany = () => {
    LoaderService.show();
    this.hideModal();
    this._companyService.deleteCompany(this.activeId).subscribe(
      (res) => {
        LoaderService.hide();
        if (res['data']) {
          this.rerender();
          this.toaster.success('success', 'Deleted successfully');
        } else {
          this.toaster.error('failed', 'Error while deleting');
        }
      },
      (e) => {
        LoaderService.hide();
      }
    );
  };
}
