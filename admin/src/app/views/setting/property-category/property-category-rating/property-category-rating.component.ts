import {Component, OnInit} from '@angular/core';
import {ToastrService} from 'ngx-toastr';

import {LoaderService, PropertyRatingService, RatingService} from '../../../../services';

@Component({
  selector: 'app-property-category-rating',
  templateUrl: './property-category-rating.component.html',
  styleUrls: ['./property-category-rating.component.scss']
})
export class PropertyCategoryRatingComponent implements OnInit {

  public properties = [];
  public ratingItems = [];
  public selectedPropertiesAllowedRatings = {};

  constructor(private _propertyRatingService: PropertyRatingService,
              private _ratingService: RatingService,
              private _toasterService: ToastrService) {
  }

  ngOnInit() {
    this.getAllRatingCategories();
    this.getPropertiesWithAllowedRatings();
  }

  setValues() {
    for (const property of this.properties) {
      this.selectedPropertiesAllowedRatings['property_' + property.id] = property.property_allowed_rating.map((rateItem) => {
        return rateItem.id;
      });
    }
  }

  getAllRatingCategories() {
    this._ratingService.getAllRatingCategories()
      .subscribe((response: any) => {
        this.ratingItems = response.data;
      });
  }

  getPropertiesWithAllowedRatings() {
    LoaderService.show();
    this._propertyRatingService.getPropertyWithAllowedRating()
      .subscribe((response: any) => {
        LoaderService.hide();
        this.properties = response.data;
        this.setValues();
      }, () => {
        LoaderService.hide();
      });
  }

  savePropertyAllowedRating(propertyId) {
    const ratingIds = this.selectedPropertiesAllowedRatings['property_' + propertyId];
    if (ratingIds.length === 0) {
      this._toasterService.error('Select At least one rating.', 'Can not save property rating!');
      return;
    }
    const postValues = {property_id: propertyId, rating_ids: ratingIds};
    LoaderService.show();
    this._propertyRatingService.setPropertyWithAllowedRating(postValues)
      .subscribe((response) => {
        LoaderService.hide();
        this._toasterService.success('Property Rating Saved', 'Success');
      }, (e) => {
        LoaderService.hide();
        this._toasterService.success('Something went wrong', 'Failed');
      });
  }

}
