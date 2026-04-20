import { Component, OnInit } from '@angular/core';
import { NavigationEnd, Router } from '@angular/router';
import { LoaderService } from './services';

@Component({
  // tslint:disable-next-line
  selector: 'body',
  template: `
    <router-outlet></router-outlet>
    <ngx-loading
      [show]="showLoader"
      [config]="{ fullScreenBackdrop: true }"
    ></ngx-loading>
  `
})
export class AppComponent implements OnInit {
  public showLoader = false;

  constructor(private router: Router) {}

  ngOnInit() {
    this.router.events.subscribe(evt => {
      if (!(evt instanceof NavigationEnd)) {
        return;
      }
      window.scrollTo(0, 0);
    });

    LoaderService.status.subscribe(toShow => {
      setTimeout(() => {
        this.showLoader = toShow;
      });
    });
  }
}
