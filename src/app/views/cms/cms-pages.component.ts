import { Component, OnInit, OnDestroy } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { CmsPagesService } from '../../services/cms-pages.service';
import { HelperService, LoaderService } from '../../services';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  templateUrl: 'cms-pages.component.html'
})
export class CmsPagesComponent implements OnInit, OnDestroy {
  pageSections = {};
  pageName = '';
  availablePages = [
    'home',
    'about',
    'property-guide',
    'property-coach',
    'contact',
    'review-guidelines',
    'terms-of-use',
    'privacy-policy'
  ];

  constructor(
    private _toasterService: ToastrService,
    private _cmsPagesService: CmsPagesService,
    private _activeatedRoute: ActivatedRoute,
    private _router: Router
  ) {}

  ngOnInit() {
    this.pageName = '';
    this._activeatedRoute.params.subscribe(param => {
      if (this.availablePages.includes(param['page'])) {
        this.pageSections = {};
        this.pageName = param['page'].replace(/-/g, '_');
        this.getPageData();
      } else {
        this._router.navigate(['/404']);
      }
    });
  }

  ngOnDestroy() {
    console.log('in destroy');
  }

  getPageData = () => {
    LoaderService.show();
    this._cmsPagesService.getCmsPageData(this.pageName).subscribe(
      (response: any) => {
        LoaderService.hide();
        const { data } = response;
        Object.keys(data).forEach(key => {
          // data[key]['section'] = HelperService.capitalizeString(
          //   data[key]['section_name'].replace(/_/g, ' ')
          // );
          data[key]['section'] = data[key]['section_name'];
          return data[key];
        });
        this.pageSections = data;
      },
      error => {
        LoaderService.hide();
      }
    );
  };

  onFormSubmit = values => {
    const fData = new FormData();
    Object.keys(values).forEach(item => {
      fData.append(item, values[item] !== null ? values[item] : '');
    });
    LoaderService.show();
    this._cmsPagesService.saveCmsPageData(fData).subscribe(
      (res: any) => {
        LoaderService.hide();
        if (res.status == 'success') {
          this._toasterService.success(
            values['section']
              ? values['section'] + ' section has been updated'
              : 'Section has been updated'
          );
        }
      },
      error => {
        LoaderService.hide();
        this._toasterService.error('Server Error');
      }
    );
  };

  getPageSectionLength = () => {
    return Object.keys(this.pageSections).length;
  };

  capitalizedPageName = () => {
    return this.pageName
      ? HelperService.capitalizeString(this.pageName.replace(/_/g, ' ')) +
          ' page'
      : '';
  };
}
