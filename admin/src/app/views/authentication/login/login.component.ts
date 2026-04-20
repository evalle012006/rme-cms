import {Component, OnInit} from '@angular/core';
import {FormControl, FormGroup, Validators} from '@angular/forms';
import {Router} from '@angular/router';
import {ToastrService} from 'ngx-toastr';

import {AuthenticationService, StorageManagerService} from '../../../services';


@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

  public loginForm: FormGroup;
  public status = true;

  constructor(private route: Router,
              private authService: AuthenticationService,
              private _toasterService: ToastrService) {
  }

  ngOnInit() {
    this.initForm();
  }

  initForm() {
    this.loginForm = new FormGroup({
      email: new FormControl(null, [Validators.required]),
      password: new FormControl(null, Validators.required),
    });
  }

  onLoginFormSubmit() {
    const credentials = this.loginForm.value;
    this.authService.login(credentials).subscribe(
      (responseData: any) => {
        if (responseData.status === 'success') {
          this._toasterService.success('Redirecting to dashboard', 'Login Success');
          StorageManagerService.storeToken(responseData.data.access_token);
          StorageManagerService.storeUser(responseData.data.user);
          this.route.navigate(['/']);
        } else {
          this._toasterService.error('Something Went Wrong', 'Login Failed');
        }
      },
      (errorData) => {
        this._toasterService.error(errorData.error.message, 'Login Failed');
      });
  }

}
