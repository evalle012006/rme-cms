import {NgModule} from '@angular/core';
import {LoginComponent} from './login/login.component';
import {SharedCustomModule} from '../../shared.module';
import {AuthenticationRouting} from './authentication.routing';


@NgModule({
  imports: [
    SharedCustomModule,
    AuthenticationRouting
  ],
  declarations: [LoginComponent]
})

export class AuthenticationModule {
}
