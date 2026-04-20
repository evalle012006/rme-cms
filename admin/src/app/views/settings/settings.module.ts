import {NgModule} from '@angular/core';
import {ProfileComponent} from './profile/profile.component';
import {SharedCustomModule} from '../../shared.module';
import {RouterModule, Routes} from '@angular/router';
import {DropzoneModule} from '../../components/dropzone/dropzone.module';
import {FormElementsModule} from '../../components/form-elements/form-elements.module';

const routes: Routes = [
  {
    path: '',
    redirectTo: 'profile',
    pathMatch: 'full',
  },
  {
    path: 'profile',
    component: ProfileComponent,
    data: {title: 'Profile'},
  }
];

@NgModule({
  declarations: [ProfileComponent],
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DropzoneModule,
    FormElementsModule,
  ]
})
export class SettingsModule {
}
