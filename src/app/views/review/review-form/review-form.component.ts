import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import {
  LoaderService,
  PropertyService,
  ReviewService,
  UserService,
} from '../../../services';
import { environment } from '../../../../environments/environment';
import { DomSanitizer } from '@angular/platform-browser';

@Component({
  templateUrl: 'review-form.component.html',
  styleUrls: ['../review.styles.scss'],
})
export class ReviewFormComponent implements OnInit {
  public reviewForm: FormGroup;
  public id;
  public sub;
  public statusOptions = [
    { label: 'Accept', value: 1 },
    { label: 'Pending', value: 0 },
  ];
  public recommendationList = [
    { label: 'Yes', value: true },
    { label: 'No', value: false },
  ];
  cmsData = null;
  public users = [];
  public states = [];
  public properties = [];
  public buyerTypes = [];
  public categories = [];
  public suburbs = [];
  public imagePath = environment.img_url;
  public isPdf = false;
  public proofIsImage = false;
  public tags = [];
  public selectedTags = [];

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private _toasterService: ToastrService,
    private reviewService: ReviewService,
    private sanitizer: DomSanitizer
  ) {}

  ngOnInit() {
    this.reviewForm = this.formBuilder.group({
      title: ['', Validators.required],
      // user_id: ['', Validators.required],
      user_fullname: [''],
      property_id: ['', Validators.required],
      category_id: [{ value: '', disabled: true }, Validators.required],
      recommendation: [false, Validators.required],
      comment: [''],
      buyer_type: ['', Validators.required],
      status: ['', Validators.required],
      state: ['', Validators.required],
      suburb: ['', Validators.required],
      post_code: ['', Validators.required],
      company: [{ value: '', disabled: true }],
      review_tags: ['', Validators.required],
      year_of_completion: [''],
    });

    this.sub = this.route.params.subscribe((params) => {
      this.id = +params['id'];
      if (this.id) {
        this.getReviewById(this.id);
      }
    });
    // this.getUsers();
    // this.getProperties();
    this.getBuyerType();
    this.getStates();
  }

  getStates() {
    LoaderService.show();
    this.reviewService.getAllStates().subscribe(
      (response: any) => {
        LoaderService.hide();
        if (response.status === 'success') {
          this.states = response.data;
        }
      },
      () => {
        LoaderService.hide();
      }
    );
  }

  getBuyerType() {
    LoaderService.show();
    this.reviewService.getBuyerType().subscribe(
      (response: any) => {
        LoaderService.hide();
        if (response.status === 'success') {
          this.buyerTypes = response.data['buyertype']['option_select'];
          this.categories = response.data['categories'];
        }
      },
      () => {
        LoaderService.hide();
      }
    );
  }

  getReviewById(id) {
    this.reviewService.getReviewById(id).subscribe(
      (response) => {
        this.reviewForm.addControl(
          'id',
          this.formBuilder.control(['', Validators.required])
        );

        const responseData = response['data'];
        this.cmsData = response['data'];
        // tslint:disable-next-line:forin
        for (const field in this.reviewForm.controls) {
          this.reviewForm.get(field).setValue(responseData[field]);
        }
        if (responseData['property']['company']) {
          this.reviewForm
            .get('company')
            .setValue(responseData['property']['company']['company_name']);
        }

        const url = this.cmsData['review_docs'];
        this.suburbs = this.cmsData['suburb_list'];
        const extension = url.substr(url.lastIndexOf('.') + 1, url.length);

        const selectedName =
          this.cmsData['users']['display_name'] +
          ' (' +
          this.cmsData['users']['firstname'] +
          ' ' +
          this.cmsData['users']['lastname'] +
          ')';
        this.reviewForm.get('user_fullname').setValue(selectedName);

        this.properties = this.cmsData['property_list'];
        this.tags = this.cmsData['tags_list'];
        this.reviewForm
          .get('review_tags')
          .setValue(this.cmsData['review_tags'].map((item) => item.id));
        this.selectedTags = this.cmsData['review_tags'];
        if (extension.toLowerCase() === 'pdf') {
          this.isPdf = true;
          this.cmsData[
            'review_docs'
          ] = this.sanitizer.bypassSecurityTrustResourceUrl(
            this.imagePath + this.cmsData['review_docs']
          );
        } else if (['jpg', 'jpeg', 'png'].includes(extension.toLowerCase())) {
          this.proofIsImage = true;
        } else {
          this.cmsData[
            'review_docs'
          ] = this.sanitizer.bypassSecurityTrustResourceUrl(
            'https://view.officeapps.live.com/op/embed.aspx?src=' +
              this.imagePath +
              this.cmsData['review_docs']
          );
        }
      },
      (error) => {}
    );
  }

  addReview() {
    this.reviewForm.markAllAsTouched();

    if (this.reviewForm.valid) {
      if (this.id) {
        const formData = { ...this.reviewForm.value };
        formData['review_tags'] = this.selectedTags;
        formData['recommendation'] =
          formData['recommendation'] == 'true' ? true : false;
        LoaderService.show();

        this.reviewService.updateReview(this.id, formData).subscribe(
          (response) => {
            LoaderService.hide();
            if (response['status'] === 'success') {
              this._toasterService.success('review Updated', 'Success');
              this.cancel();
            }
          },
          (error) => {
            LoaderService.hide();
            this.setServeErrors(error.error);
            this._toasterService.error('review Update Failed', 'Failed');
          }
        );
      }
    }
  }

  setServeErrors(errorResponse) {
    for (const controlKey of Object.keys(errorResponse.errors)) {
      this.reviewForm
        .get(controlKey)
        .setErrors({ serverErrors: errorResponse.errors[controlKey] });
    }
  }

  cancel() {
    if (this.reviewForm.get('status').value) {
      this.router.navigate(['/', 'review', 'approve']);
    } else {
      this.router.navigate(['/', 'review', 'pending']);
    }
  }

  onChangePostcode = (e) => {
    const postCode = e.target.value;
    this.reviewService.getSuburbs(postCode).subscribe((res) => {
      if (res['data']) {
        this.suburbs = res['data'];
        this.reviewForm.get('suburb').setValue('');
      } else {
        this.suburbs = [];
      }
    });
  };
  onAddNewItem = (items) => {
    this.selectedTags = items;
  };
}
