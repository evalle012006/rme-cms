import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DataTablesModule } from 'angular-datatables';
import { NgSelectModule } from '@ng-select/ng-select';
import { SharedCustomModule } from '../../shared.module';
import { FormElementsModule } from '../../components/form-elements/form-elements.module';
import { DropzoneModule } from '../../components/dropzone/dropzone.module';
import { ReviewRatioComponent } from './home/review-ratio/review-ratio.component';
import { CommonFormSection } from './common-forms/common-form-section.component';
import { OurMissionComponent } from './about/our-mission/our-mission.component';
import { CmsPagesComponent } from './cms-pages.component';

const routes: Routes = [
  {
    path: '',
    data: {
      title: 'Cms'
    },
    children: [
      {
        path: ':page',
        component: CmsPagesComponent,
        data: {
          title: ''
        }
      }
    ]
  }
];

@NgModule({
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
    FormElementsModule,
    NgSelectModule,
    DropzoneModule
  ],
  providers: [],
  declarations: [
    CmsPagesComponent,
    CommonFormSection,
    // for home page
    ReviewRatioComponent,

    // for about page
    OurMissionComponent
  ]
})
export class CmsModule {}
