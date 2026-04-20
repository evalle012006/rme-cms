import {
  AfterViewInit,
  Component,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {
  DatatableService,
  LoaderService,
  PropertyCategoryService,
  PropertyService,
} from '../../services';
import { DataTableDirective } from 'angular-datatables';
import { ActivatedRoute, Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { Subject } from 'rxjs';

@Component({
  selector: 'app-property',
  templateUrl: './property.component.html',
  styleUrls: ['./property.component.scss'],
})
export class PropertyComponent implements AfterViewInit, OnDestroy, OnInit {
  activeRow: any = {};

  @ViewChild(DataTableDirective) public dtElement: DataTableDirective;
  public dtOptions: DataTables.Settings = {};
  public dtTrigger: Subject<any> = new Subject();
  modalRef: BsModalRef;
  public markerIcon: any = {
    url: '/assets/img/marker.png',
    scaledSize: {
      width: 32,
      height: 30,
    },
  };

  public markers = [];

  public properties = [];

  public importForm: FormGroup;
  fileName;

  constructor(
    private _datatableService: DatatableService,
    private _router: Router,
    private toaster: ToastrService,
    private modalService: BsModalService,
    private _propertyService: PropertyService,
    private _formBuilder: FormBuilder,
    private _activatedRouter: ActivatedRoute
  ) {}

  ngOnInit() {
    this.getProperty();
    this.importForm = this._formBuilder.group({
      import_file: ['', Validators.required],
    });
    console.log(this.importForm);
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

  getProperty() {
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
          .getTableData(dataTablesParameters, 'datatable-property-pending')
          .subscribe((resp) => {
            this.properties = resp.data;
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
        { name: 'project_name' },
        { name: 'id', orderable: false },
        { name: 'id', orderable: false },
        { name: 'id', orderable: false },
      ],
    };
  }

  setMarkers() {
    this.markers = [];
    for (const property of this.properties) {
      if (property.lat && property.long) {
        this.markers.push({ lat: property.lat, lng: property.long });
      }
    }
  }

  redirectToEditProperty(propertyId) {
    this._router.navigate(['/', 'property', 'edit', propertyId]);
  }

  onSelect(event) {
    this.fileName = event.target.files[0];
  }

  importModel(template) {
    this.modalRef = this.modalService.show(template);
  }

  closeModel() {
    this.importForm.reset();
    this.modalRef.hide();
  }

  importSubmit() {
    this.importForm.markAllAsTouched();
    if (this.importForm.valid) {
      const postValues = this.importForm.value;
      const propertyFormData = new FormData();
      propertyFormData.append('import_file', this.fileName);
      LoaderService.show();
      this._propertyService.importPropertyData(propertyFormData).subscribe(
        (response) => {
          LoaderService.hide();
          this.rerender();
          this.closeModel();
          if (Object.keys(response['data']).length === 0) {
            this.toaster.success('success', 'import successfully');
          } else {
            this.toaster.error('failed', 'compant not found');
          }
        },
        (e) => {
          LoaderService.hide();
          if (e['error']) {
            this.closeModel();
            this.setServeErrors(e['error']);
          }
        }
      );
    }
  }

  setServeErrors(errorResponse) {
    const arr: any = [];
    for (const controlKey of errorResponse.errors) {
      arr.push(controlKey);
    }
    this.toaster.error('error', arr);
  }

  showModal = (row, templefRef) => {
    this.modalRef = this.modalService.show(templefRef);
    this.activeRow = row;
  };

  changeStatus = () => {
    this.modalRef.hide();
    this._propertyService
      .approveProperty(this.activeRow.id)
      .subscribe((res) => {
        this.rerender();
      });
  };

  hideModal = () => {
    this.modalRef.hide();
  };

  deleteProperty = () => {
    LoaderService.show();
    this.closeModel();
    this._propertyService.deleteProperty(this.activeRow.id).subscribe(
      (response) => {
        LoaderService.hide();
        this.rerender();
        if (response['data']) {
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
