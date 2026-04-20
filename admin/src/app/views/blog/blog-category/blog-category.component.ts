import {
  AfterViewInit,
  Component,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import { Subject } from 'rxjs';

import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';
import { DataTableDirective } from 'angular-datatables';

import { DatatableService, SettingService } from '../../../services';

@Component({
  templateUrl: 'blog-category.component.html',
})
export class BlogCategoryComponent implements OnInit, AfterViewInit, OnDestroy {
  blog_category: any = [];
  modalRef: BsModalRef;

  private id_to_delete = null;

  @ViewChild(DataTableDirective)
  dtElement: DataTableDirective;
  dtOptions: DataTables.Settings = {};
  dtTrigger: Subject<any> = new Subject();

  constructor(
    private datatableService: DatatableService,
    private modalService: BsModalService,
    private settingService: SettingService
  ) {}

  ngOnInit() {
    this.getAllBlogCategory();
  }

  ngAfterViewInit(): void {
    this.dtTrigger.next();
  }

  ngOnDestroy(): void {
    this.dtTrigger.unsubscribe();
  }

  getAllBlogCategory() {
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
          .getTableData(dataTablesParameters, 'datatable-blog-category')
          .subscribe((resp) => {
            this.blog_category = resp.data;
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
        { name: 'name', orderable: false },
      ],
    };
  }

  deleteModel(template, delete_id) {
    this.id_to_delete = delete_id;
    this.modalRef = this.modalService.show(template);
  }

  deleteRating() {
    if (this.id_to_delete !== null) {
      this.settingService.deleteRating(this.id_to_delete).subscribe(
        (response) => {
          if (response.status === 'success') {
            this.closeModel();
            this.rerender();
          }
        },
        (error) => {}
      );
    }
  }

  rerender() {
    this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
      dtInstance.destroy();
      this.dtTrigger.next();
    });
  }

  closeModel() {
    this.id_to_delete = null;
    this.modalRef.hide();
  }
}
