import {
  AfterViewInit,
  Component,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import { DataTableDirective } from 'angular-datatables';
import { ActivatedRoute, Router } from '@angular/router';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { Subject } from 'rxjs';
import {
  DatatableService,
  LoaderService,
  PropertyService,
} from '../../../services';

@Component({
  selector: 'app-property',
  templateUrl: './property-approve.component.html',
  styleUrls: ['./property-approve.component.scss'],
})
export class PropertyApproveComponent
  implements AfterViewInit, OnDestroy, OnInit {
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
          .getTableData(dataTablesParameters, 'datatable-property-approve')
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

  setServeErrors(errorResponse) {
    const arr: any = [];
    for (const controlKey of errorResponse.errors) {
      arr.push(controlKey);
    }
    this.toaster.error('error', arr);
  }
}
