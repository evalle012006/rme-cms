import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder } from '@angular/forms';
import { UserService } from '../../../services';

@Component({
  templateUrl: 'user-edit.component.html',
  styleUrls: ['../user-add/user-add.component.scss']
})
export class UserEditComponent implements OnInit {

  public userType = null;
  public userDetails = {};
  private userId;

  constructor(private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private userService: UserService) {
  }

  ngOnInit() {
    this.userId = +this.route.snapshot.params['id'];
    this.getUserById();
  }

  getUserById() {
    if (!this.userId) {
      return;
    }
    this.userService.getUserById(this.userId)
      .subscribe(response => {
        this.userType = response.data.roles[0]['name'] === 'reviewer' ? 'reviewer' : 'builder';
        this.userDetails = response.data;
      }, error => {
      });
  }

}
