import {Component, OnInit} from '@angular/core';

@Component({
  templateUrl: 'user-add.component.html',
  styleUrls: ['user-add.component.scss']
})
export class UserAddComponent implements OnInit {

  public userType = null;

  constructor() {
  }

  ngOnInit() {
  }

  changeType(type) {
    if (this.userType === type) {
      this.userType = null;
    } else {
      this.userType = type;
    }
  }

}
