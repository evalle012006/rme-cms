import { Injectable } from '@angular/core';
import * as moment from 'moment';

@Injectable({
  providedIn: 'root'
})
export class HelperService {
  static defaultLatitude = -33.8688;
  static defaultLongitude = 151.2093;

  constructor() {}

  static toSlug(str: string) {
    return str
      .toLowerCase()
      .replace(/ /g, '-')
      .replace(/[^\w-]+/g, '');
  }

  static createFormData(
    object: Object,
    form?: FormData,
    namespace?: string
  ): FormData {
    const formData = form || new FormData();
    for (const property in object) {
      if (!object.hasOwnProperty(property) || !object[property]) {
        continue;
      }
      const formKey = namespace ? `${namespace}[${property}]` : property;
      if (object[property] instanceof Date) {
        formData.append(formKey, moment(object[property]).format());
      } else if (
        typeof object[property] === 'object' &&
        !(object[property] instanceof File)
      ) {
        this.createFormData(object[property], formData, formKey);
      } else {
        formData.append(formKey, object[property]);
      }
    }
    return formData;
  }

  fileToBase64(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () => resolve(reader.result);
      reader.onerror = error => reject(error);
    });
  }

  static setCmsFormValues = (form, valuesObj) => {
    Object.keys(form.controls).forEach(item => {
      if (![undefined, null].includes(valuesObj[item])) {
        form.get(item).setValue(valuesObj[item]);
      }
    });
  };

  static capitalizeString = str => {
    if (str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }
    return '';
  };
}
