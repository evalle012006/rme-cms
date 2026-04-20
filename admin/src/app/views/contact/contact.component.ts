import {
  AfterViewInit,
  Component,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import { DataTableDirective } from 'angular-datatables';
import { Subject } from 'rxjs';
import { DatatableService, PropertyService } from '../../services';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { BsModalService } from 'ngx-bootstrap/modal';
import { FormBuilder } from '@angular/forms';

@Component({
  selector: 'app-contact',
  templateUrl: './contact.component.html',
  styleUrls: ['./contact.component.scss'],
})
export class ContactComponent implements AfterViewInit, OnDestroy, OnInit {
  @ViewChild(DataTableDirective) public dtElement: DataTableDirective;
  public dtOptions: DataTables.Settings = {};
  public dtTrigger: Subject<any> = new Subject();
  public contact = [];

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
    this.getContactDetails();
  }

  ngAfterViewInit(): void {
    this.dtTrigger.next();
  }

  ngOnDestroy(): void {
    this.dtTrigger.unsubscribe();
  }

  getContactDetails() {
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
          .getTableData(dataTablesParameters, 'datatable-contact-details')
          .subscribe((resp) => {
            this.contact = resp.data;
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
        { name: 'firstname' },
        { name: 'lastname' },
        { name: 'email' },
        { name: 'subject' },
      ],
    };
  }

  trimMessage(message) {
    if (message.length > 25) {
      return message.slice(0, 30).trim() + '...';
    }
    return message.trim();
  }
}
