import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {AuthGuard} from './guards';

import {DefaultLayoutComponent} from './containers/default-layout';
import {SimpleLayoutComponent} from './containers/simple-layout';

export const routes: Routes = [
  {
    path: '',
    component: DefaultLayoutComponent,
    data: {
      title: 'Home'
    },
    children: [
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full'
      },
      {
        path: 'dashboard',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/dashboard/dashboard.module').then(
            m => m.DashboardModule
          )
      },
      {
        path: 'users',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/user/user.module').then(m => m.UserModule)
      },
      {
        path: 'builder',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/builder/builder.module').then(m => m.BuilderModule)
      },
      {
        path: 'company',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/company/company.module').then(m => m.CompanyModule)
      },
      {
        path: 'property',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/property/property.module').then(m => m.PropertyModule)
      },
      {
        path: 'advertise',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/advertise/advertise.module').then(m => m.AdvertiseModule)
      },
      {
        path: 'property-category',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import(
            './views/setting/property-category/property-category.module'
            ).then(m => m.PropertyCategoryModule)
      },
      {
        path: 'blog-category',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/blog/blog-category/blog-category.module').then(
            m => m.BlogCategoryModule
          )
      },
      {
        path: 'blogs',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/blog/blogs/blogs.module').then(m => m.BlogsModule)
      },
      {
        path: 'rating',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/setting/rating/rating.module').then(
            m => m.RatingModule
          )
      },
      {
        path: 'tags',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/tag/tag.module').then(m => m.TagModule)
      },
      {
        path: 'contact',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/contact/contact.module').then(m => m.ContactModule)
      },
      {
        path: 'cms',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/cms/cms.module').then(m => m.CmsModule)
      },
      {
        path: 'review',
        canActivate: [AuthGuard],
        loadChildren: () =>
          import('./views/review/review.module').then(m => m.ReviewModule)
      },
      {
        path: 'setting',
        canActivate: [AuthGuard],
        loadChildren: () => import('./views/settings/settings.module').then(m => m.SettingsModule)
      }
    ]
  },
  {
    path: 'auth',
    component: SimpleLayoutComponent,
    children: [
      {
        path: '',
        loadChildren: () =>
          import('./views/authentication/authentication.module').then(
            m => m.AuthenticationModule
          )
      }
    ]
  },
  {
    path: '404',
    component: SimpleLayoutComponent,
    children: [
      {
        path: '',
        loadChildren: () =>
          import('./views/error/404.module').then(m => m.P404Module)
      }
    ]
  },
  {
    path: '**',
    redirectTo: '404',
    pathMatch: 'full'
  }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule {
}
