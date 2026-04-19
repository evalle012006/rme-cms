import {
  Component,
  ElementRef,
  NgZone,
  OnInit,
  ViewChild,
} from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormArray, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { MapsAPILoader, MouseEvent } from '@agm/core';

import {
  CompanyService,
  HelperService,
  LoaderService,
  PropertyCategoryService,
  PropertyService,
  RatingService,
  OptionService,
  ReviewService,
} from '../../../services';
import { concat, Observable, of, Subject } from 'rxjs';
import {
  catchError,
  debounceTime,
  distinctUntilChanged,
  map,
  switchMap,
  tap,
} from 'rxjs/operators';

@Component({
  selector: 'app-property-form',
  templateUrl: './property-form.component.html',
  styleUrls: ['./property-form.component.scss'],
})
export class PropertyFormComponent implements OnInit {
  @ViewChild('search') public searchElementRef: ElementRef;

  public mapZoom = 12;
  private geoCoder;
  public marker: any = {
    url: '/assets/img/marker.png',
    scaledSize: {
      width: 32,
      height: 30,
    },
  };

  public propertyCategories = [];
  public suburbsOptions = [];
  public propertyForm: FormGroup;
  public propertyGalForm: FormGroup;
  public editMode = false;
  public propertyId = 0;

  public propertyArr = null;
  public statusOptions = [
    { label: 'Pending', value: 0 },
    { label: 'Approved', value: 1 },
  ];

  public prevImages = [];

  public companyOption: Observable<{} | any[]>;
  public companyLoading: boolean = false;
  public companyInput: Subject<string> = new Subject<string>();
  public companyList = [];
  public states = [];

  constructor(
    private _propertyCategoryService: PropertyCategoryService,
    private _propertyService: PropertyService,
    private _formBuilder: FormBuilder,
    private _router: Router,
    private _ratingService: RatingService,
    private _toasterService: ToastrService,
    private _activatedRouter: ActivatedRoute,
    private mapsAPILoader: MapsAPILoader,
    private _companyService: CompanyService,
    private ngZone: NgZone,
    private _optionService: OptionService,
    private reviewService: ReviewService
  ) {}

  ngOnInit() {
    this.setForm();
    this.propertyId = +this._activatedRouter.snapshot.params['id'];
    this.editMode = this._activatedRouter.snapshot.data['edit'];
    this.getPropertyCategories();
    this.initLocationSearch();
    this.getAllompany();
    this.getStates();
    this.companyOption = concat(
      of([]),
      this.companyInput.pipe(
        debounceTime(2000),
        distinctUntilChanged(),
        tap(() => (this.companyLoading = true)),
        switchMap((searchItem) =>
          this._companyService.searchCompany(searchItem).pipe(
            catchError(() => of([])),
            tap(() => (this.companyLoading = false)),
            map((result: any) => result.data)
          )
        )
      )
    );
  }

  getPropertyCategories() {
    LoaderService.show();
    this._propertyCategoryService.getAllPropertyCategory().subscribe(
      (response: any) => {
        LoaderService.hide();
        if (response.status === 'success') {
          this.propertyCategories = response.data;
          if (this.editMode) {
            this.getProperty();
          }
        }
      },
      () => {
        LoaderService.hide();
      }
    );
  }

  getAllompany() {
    this._companyService.getAllCompanies().subscribe(
      (response) => {
        this.companyList = response['data'];
      },
      (error) => {}
    );
  }

  setForm() {
    this.propertyForm = this._formBuilder.group({
      property_categories: ['', [Validators.required]],
      project_name: ['', [Validators.required]],
      // developer_name: ['', [Validators.required]],
      developer_name: [''],
      // email: [''],
      phone: [''],
      website_url: [''],
      description: [''],
      address: ['', [Validators.required]],
      country: ['', [Validators.required]],
      state: ['', [Validators.required]],
      city: ['', [Validators.required]],
      post_code: [''],
      lat: [HelperService.defaultLatitude],
      long: [HelperService.defaultLongitude],
      status: ['', Validators.required],
      suburb: ['', Validators.required],
      logo: [''],
      // 'image_to_add': this._formBuilder.array([]),
      // 'image_to_remove': this._formBuilder.array([]),
    });

    this.propertyGalForm = this._formBuilder.group({
      id: [null],
      image_to_add: this._formBuilder.array([]),
      image_to_remove: this._formBuilder.array([]),
    });
    this.propertyForm.get('lat').disable();
    this.propertyForm.get('long').disable();
  }

  getProperty() {
    LoaderService.show();
    this._propertyService.getPropertyById(this.propertyId).subscribe(
      (response: any) => {
        LoaderService.hide();
        this.propertyArr = response.data;
        // fetch suburbs of postcode if postcode is available
        this.suburbsOptions = [
          { id: this.propertyArr['suburb'], text: this.propertyArr['suburb'] },
        ];

        this.setFormValue(response.data);
        this.setGallery(response.data.gallery);
      },
      () => {
        LoaderService.hide();
      }
    );
  }

  setFormValue(values) {
    // tslint:disable-next-line:forin
    for (const field in this.propertyForm.controls) {
      if (values[field] !== null) {
        switch (field) {
          case 'lat':
          case 'long':
            this.propertyForm.get(field).setValue(+values[field]);
            break;

          case 'property_categories':
            this.propertyForm
              .get(field)
              .setValue(
                values['property_allowed_category'].map((catItem) => catItem.id)
              );
            break;
          default:
            this.propertyForm.get(field).setValue(values[field]);
        }
      }
    }

    if (!this.propertyForm.get('lat').value) {
      this.propertyForm.get('lat').setValue(HelperService.defaultLatitude);
    }

    if (!this.propertyForm.get('long').value) {
      this.propertyForm.get('long').setValue(HelperService.defaultLongitude);
    }
  }

  cancel() {
    if (this.propertyForm.get('status').value == '1') {
      this._router.navigate(['/', 'property', 'approve']);
    } else {
      this._router.navigate(['/', 'property', 'pending']);
    }
  }

  formSubmit() {
    if (this.propertyForm.invalid) {
      this.propertyForm.markAllAsTouched();
      return;
    }
    LoaderService.show();
    const formValues = this.propertyForm.value;

    const userFormData = new FormData();

    for (const field in formValues) {
      if (
        formValues.hasOwnProperty(field) &&
        field !== 'property_categories' &&
        field !== 'logo'
      ) {
        userFormData.append(field, formValues[field]);
      }
    }
    userFormData.append(
      'property_categories',
      JSON.stringify(formValues['property_categories'])
    );

    if (formValues['logo'] !== null) {
      userFormData.append('logo', formValues['logo']);
    } else {
      userFormData.append('logo', '');
    }
    if (this.editMode) {
      this._propertyService
        .updateProperty(this.propertyId, userFormData)
        .subscribe(
          (response) => {
            LoaderService.hide();
            this._toasterService.success('Property Updated', 'Success');
            this.cancel();
          },
          (e) => {
            LoaderService.hide();
            this.setServeErrors(e.error);
            this._toasterService.error('Property Update Failed', 'Failed');
          }
        );
    } else {
      this._propertyService.addProperty(userFormData).subscribe(
        (response) => {
          const property_id = response['data']['id'];
          LoaderService.hide();
          this._toasterService.success('Property Added', 'Success');
          this.cancel();
        },
        (e) => {
          LoaderService.hide();
          this.setServeErrors(e.error);
          this._toasterService.error('Property Add Failed', 'Failed');
        }
      );
    }
  }

  setServeErrors(errorResponse) {
    for (const controlKey of Object.keys(errorResponse.errors)) {
      this.propertyForm
        .get(controlKey)
        .setErrors({ serverErrors: errorResponse.errors[controlKey] });
    }
  }

  setLatLong(lat, lng) {
    this.propertyForm.get('lat').setValue(lat);
    this.propertyForm.get('long').setValue(lng);
  }

  setGallery(gallery) {
    for (const gal of gallery) {
      this.prevImages.push({ id: gal.id, path: gal.full_property_image });
    }
  }

  initLocationSearch() {
    this.mapsAPILoader.load().then(() => {
      this.setCurrentLocation();
      this.geoCoder = new google.maps.Geocoder();

      const defaultBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(-33.8902, 151.1759),
        new google.maps.LatLng(-33.8474, 151.2631)
      );

      const autocomplete = new google.maps.places.Autocomplete(
        this.searchElementRef.nativeElement,
        {
          bounds: defaultBounds,
          types: ['geocode'],
          componentRestrictions: { country: 'au' },
        }
      );

      autocomplete.addListener('place_changed', () => {
        this.ngZone.run(() => {
          // get the place result
          const place: google.maps.places.PlaceResult = autocomplete.getPlace();
          // verify result
          if (place.geometry === undefined || place.geometry === null) {
            return;
          }

          // set latitude, longitude and zoom
          this.setLatLong(
            place.geometry.location.lat(),
            place.geometry.location.lng()
          );
          this.mapZoom = 12;

          const latitude = place.geometry.location.lat();
          const longitude = place.geometry.location.lng();
          const latlng = new google.maps.LatLng(latitude, longitude);
          var geocoder = (geocoder = new google.maps.Geocoder());
          const that = this;
          geocoder.geocode({ latLng: latlng }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
              if (results[0]) {
                var address = results[0].formatted_address;

                const pin =
                  results[0].address_components[
                    results[0].address_components.length - 1
                  ].long_name;
                const country =
                  results[0].address_components[
                    results[0].address_components.length - 2
                  ].long_name;
                const state =
                  results[0].address_components[
                    results[0].address_components.length - 3
                  ].long_name;
                const city =
                  results[0].address_components[
                    results[0].address_components.length - 5
                  ].long_name;
                const addressStreet = address.split(',')[0].replace(',', '');
                that.propertyForm.get('address').setValue(addressStreet);
                that.propertyForm.get('post_code').setValue(pin);
                that.propertyForm.get('country').setValue(country);
                that.propertyForm.get('state').setValue(state);
                that.propertyForm.get('city').setValue(city);
                that.propertyForm.get('suburb').setValue('');
              }
            }
          });
        });
      });
    });
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

  markerChanged($event: MouseEvent) {
    this.setLatLong($event.coords.lat, $event.coords.lng);
    this.getAddress($event.coords.lat, $event.coords.lng);
  }

  private setCurrentLocation() {
    if ('geolocation' in navigator) {
      navigator.geolocation.getCurrentPosition((position) => {
        this.mapZoom = 8;
        this.setLatLong(position.coords.latitude, position.coords.longitude);
        this.getAddress(position.coords.latitude, position.coords.longitude);
      });
    }
  }

  getAddress(latitude, longitude) {
    this.geoCoder.geocode(
      { location: { lat: latitude, lng: longitude } },
      (results, status) => {
        if (status === 'OK') {
          if (results[0]) {
            this.mapZoom = 12;
            // console.log('address - ', results[0].formatted_address);
          }
        } else {
          // console.log('map error status', status);
        }
      }
    );
  }

  handleImage(file) {
    const control = <FormArray>this.propertyGalForm.get('image_to_add');
    control.push(this._formBuilder.control(file));
  }

  onFileRemove(val: any) {
    if (val.id !== 0) {
      const control = <FormArray>this.propertyGalForm.get('image_to_remove');
      control.push(this._formBuilder.control(val.id));
    }
  }
  onChangePostcode(code) {
    this.suburbsOptions = [];
    this._optionService.getSubUrbsByPostCode(code).subscribe(
      (res) => {
        this.suburbsOptions = res['results'];
      },
      (error) => {
        console.log(error);
      }
    );
    // console.log(code);
  }
  onSelectAddress = () => {
    console.log('in');
  };
}
