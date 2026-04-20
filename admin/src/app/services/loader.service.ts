import {Injectable} from '@angular/core';
import {BehaviorSubject} from 'rxjs';

@Injectable()
export class LoaderService {
  public static status: BehaviorSubject<boolean> = new BehaviorSubject<boolean>(false);

  static display(value: boolean) {
    this.status.next(value);
  }

  static show() {
    this.display(true);
  }

  static hide() {
    this.display(false);
  }
}
