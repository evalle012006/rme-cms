import {Component, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {HelperService, LoaderService, UserService} from '../../../services';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss']
})
export class ProfileComponent implements OnInit {
  public profileForm: FormGroup;
  public profileData = null;

  constructor(private _fb: FormBuilder, private _userService: UserService) {
  }

  ngOnInit() {
    this.initForm();
    this.getUser();
  }

  private initForm() {
    this.profileForm = this._fb.group({
      firstname: [null, Validators.required],
      lastname: [null, Validators.required],
      email: [null],
      profile_image: [null],
      password: [null]
    });
  }

  private getUser() {
    const user = JSON.parse(localStorage.getItem('user'));

    if (user === null) {
      return;
    }

    LoaderService.show();
    this._userService.getUserById(user.id).subscribe((response) => {
      LoaderService.hide();
      this.profileData = response.data;
      this.setFormValue();
    }, () => {
      LoaderService.hide();
    });
  }

  private setFormValue() {
    if (this.profileForm && this.profileData) {
      this.profileForm.get('firstname').setValue(this.profileData.firstname);
      this.profileForm.get('lastname').setValue(this.profileData.lastname);
      this.profileForm.get('email').setValue(this.profileData.email);
      this.profileForm.get('profile_image').setValue(this.profileData.profile_image);
    }
  }

  public saveProfile() {
    LoaderService.show();

    const postValues = HelperService.createFormData(this.profileForm.value);

    this._userService.updateProfile(postValues).subscribe((response) => {
      LoaderService.hide();
      localStorage.setItem('user', JSON.stringify(response.data));
      window.location.reload();
    }, () => {
      LoaderService.hide();
    });
  }

  public sendPasswordSetLink() {
    LoaderService.show();
    this._userService.sendResetPasswordEmail().subscribe((response) => {
      LoaderService.hide();
      if (response.sent) {
        localStorage.clear();
        window.location.reload();
      }
    }, () => {
      LoaderService.hide();
    });
  }
}
