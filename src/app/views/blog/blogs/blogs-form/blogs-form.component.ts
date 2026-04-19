import { Component, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { BlogService, LoaderService } from '../../../../services';
import { ToastrService } from 'ngx-toastr';
import * as moment from 'moment';

@Component({
  templateUrl: 'blogs-form.component.html',
  styleUrls: ['blog-form.component.scss'],
})
export class BlogsFormComponent implements OnInit {
  public blogForm: FormGroup;
  public id;
  public sub;
  public blog_categories = [];
  public statusOptions = [
    { label: 'Published', value: 0 },
    { label: 'Drafted', value: 1 },
    { label: 'Archieve', value: 2 },
  ];

  @ViewChild('myPond') myPond: any;
  blogData = null;

  session_user = JSON.parse(localStorage.getItem('user'));

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private _toasterService: ToastrService,
    private blogService: BlogService
  ) {}

  ngOnInit() {
    this.blogForm = this.formBuilder.group({
      title: ['', Validators.required],
      feature_image: ['', Validators.required],
      description: ['', Validators.required],
      blog_date: [moment().toDate(), Validators.required],
      status: ['', Validators.required],
      seo_title: ['', Validators.required],
      seo_description: ['', Validators.required],
      seo_keywords: ['', Validators.required],
      category_id: ['', Validators.required],
      author_name: ['', Validators.required],
    });

    this.sub = this.route.params.subscribe((params) => {
      this.id = +params['id'];
      if (this.id) {
        this.getBlogById(this.id);
      }
    });
    this.getBlogCategories();
  }

  getBlogById(id) {
    this.blogService.getBlogById(id).subscribe(
      (response) => {
        this.blogForm.addControl(
          'id',
          this.formBuilder.control(['', Validators.required])
        );
        const responseData = response['data'];
        this.blogData = response['data'];
        // tslint:disable-next-line:forin
        for (const field in this.blogForm.controls) {
          switch (field) {
            case 'blog_categories':
              this.blogForm
                .get(field)
                .setValue(responseData[field].map((item) => item.id));
              break;
            case 'blog_date':
              this.blogForm.get(field).setValue(new Date(responseData[field]));
              break;
            default:
              this.blogForm.get(field).setValue(responseData[field]);
          }
        }
      },
      (error) => {}
    );
  }

  addBlog() {
    this.blogForm.markAllAsTouched();

    if (this.blogForm.valid) {
      const postValues = Object.assign({}, this.blogForm.value);

      postValues.blog_date = moment(postValues.blog_date).format();

      const userFormData = new FormData();

      for (const field in postValues) {
        if (postValues.hasOwnProperty(field)) {
          userFormData.append(field, postValues[field]);
        }
      }
      if (postValues.feature_image === null) {
        userFormData.delete('feature_image');
      }

      if (this.id) {
        // userFormData.append('id', this.id);
        LoaderService.show();
        this.blogService.updateBlog(userFormData).subscribe(
          (response) => {
            LoaderService.hide();
            if (response['status'] === 'success') {
              this._toasterService.success('Blog Updated', 'Success');
              this.cancel();
            }
          },
          (error) => {
            LoaderService.hide();
            this.setServeErrors(error.error);
            this._toasterService.error('Blog Update Failed', 'Failed');
          }
        );
      } else {
        LoaderService.show();
        userFormData.append('created_by', this.session_user.id);
        this.blogService.addBlog(userFormData).subscribe(
          (response) => {
            LoaderService.hide();
            if (response['status'] === 'success') {
              this._toasterService.success('Blog Added', 'Success');
              this.cancel();
            }
          },
          (error) => {
            LoaderService.hide();
            this.setServeErrors(error.error);
            this._toasterService.error('Blog Add Failed', 'Failed');
          }
        );
      }
    }
  }

  setServeErrors(errorResponse) {
    for (const controlKey of Object.keys(errorResponse.errors)) {
      this.blogForm
        .get(controlKey)
        .setErrors({ serverErrors: errorResponse.errors[controlKey] });
    }
  }

  cancel() {
    this.router.navigate(['/', 'blogs']);
  }

  getBlogCategories() {
    LoaderService.show();
    this.blogService.getAllBlogCategory().subscribe(
      (response: any) => {
        LoaderService.hide();
        if (response.status === 'success') {
          this.blog_categories = response.data;
        }
      },
      () => {
        LoaderService.hide();
      }
    );
  }
}
