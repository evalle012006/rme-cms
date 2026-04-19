import {Component} from '@angular/core';

import {navItems} from '../../_nav';
import {Router} from '@angular/router';
import {StorageManagerService} from '../../services';

@Component({
  selector: 'app-dashboard',
  templateUrl: './default-layout.component.html'
})
export class DefaultLayoutComponent {
  public minimized = false;
  public navItems = [...navItems];
  public user = null;

  constructor(private _router: Router) {
    this.user = JSON.parse(localStorage.getItem('user'));
  }

  toggleMinimize(e) {
    this.minimized = e;
  }

  logout() {
    StorageManagerService.clearAll();
    this._router.navigate(['/', 'auth', 'login']);
  }
}
