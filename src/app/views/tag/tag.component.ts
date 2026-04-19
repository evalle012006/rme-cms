import {
  AfterViewInit,
  Component,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import { DataTableDirective } from 'angular-datatables';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';

import { DatatableService } from '../../services';
import { TagFormComponent } from './tag-form/tag-form.component';
import { Subject } from 'rxjs';

@Component({
  selector: 'app-tag',
  templateUrl: './tag.component.html',
  styleUrls: ['./tag.component.scss'],
})
export class TagComponent implements AfterViewInit, OnDestroy, OnInit {
  @ViewChild(DataTableDirective) public dtElement: DataTableDirective;
  public dtOptions: DataTables.Settings = {};
  public dtTrigger: Subject<any> = new Subject();

  public tags = [];

  public tagModalRef: BsModalRef;

  constructor(
    private _datatableService: DatatableService,
    private _modalService: BsModalService
  ) {}

  ngOnInit() {
    this.getTags();
    this._modalService.onHide.subscribe(() => {
      this.rerender();
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

  getTags() {
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
        this._datatableService
          .getTableData(dataTablesParameters, 'datatable-tags')
          .subscribe((resp) => {
            this.tags = resp.data;
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
        { name: 'name' },
        { name: 'type' },
        { name: 'id', orderable: false },
      ],
    };
  }

  openAddModal() {
    const initialState = {
      editMode: false,
      tagId: null,
      tag: null,
    };

    this.tagModalRef = this._modalService.show(TagFormComponent, {
      initialState,
    });
  }

  openEditModal(tag) {
    const initialState = {
      editMode: true,
      tagId: tag.id,
      tag: tag,
    };

    this.tagModalRef = this._modalService.show(TagFormComponent, {
      initialState,
    });
  }
}
