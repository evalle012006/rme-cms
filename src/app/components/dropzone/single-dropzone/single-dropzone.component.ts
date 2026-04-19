import {
  Component,
  ElementRef,
  EventEmitter,
  Input,
  OnInit,
  Output,
  ViewChild
} from "@angular/core";
import { FormControl } from "@angular/forms";

import { HelperService } from "../../../services";
import { ToastrService } from "ngx-toastr";

@Component({
  selector: "app-single-dropzone",
  templateUrl: "./single-dropzone.component.html",
  styleUrls: ["./single-dropzone.component.scss", "../dropzone.component.scss"]
})
export class SingleDropzoneComponent implements OnInit {
  @ViewChild("mediaInput") mediaInput: ElementRef;

  @Input() control: FormControl = null;
  @Input() allowedFileType = ["jpg", "png"];
  @Input() invalidFileText = "";
  @Input() placeHolderText = "";
  @Input() onDragText = "";
  @Input() path: string = null;

  @Output() fileHandle = new EventEmitter();
  @Output() fileSelectionInitiated = new EventEmitter();
  @Output() fileRemoved = new EventEmitter();

  public fileDragOver: boolean = false;

  constructor(
    private _helperService: HelperService,
    private _toasterService: ToastrService
  ) {}

  ngOnInit(): void {}

  onMediaHandle(files) {
    this.fileSelectionInitiated.emit(files.length);
    for (let i = 0; i < files.length; i++) {
      const fileToUpload = files.item(i);
      if (fileToUpload) {
        if (
          fileToUpload["name"] !== undefined ||
          fileToUpload["name"] !== null
        ) {
          const fileExt = fileToUpload["name"].split(".").pop();
          if (this.allowedFileType.includes(fileExt.toLowerCase())) {
            this.fileHandle.emit(fileToUpload);
            this.setImagePreview(fileToUpload);
          } else {
            this.fileHandle.emit(null);
            this._toasterService.warning(
              this.invalidFileText,
              "File Type Not allowed!"
            );
          }
        }
      }
    }
    this.mediaInput.nativeElement.value = "";
  }

  async setImagePreview(file) {
    this.control.setValue(file);
    const result: any = await this._helperService.fileToBase64(file);
    if (result instanceof Error) {
      return;
    }

    this.path = result;
  }

  onImageRemove() {
    this.path = null;
    this.control.setValue(null);
    this.control.markAsTouched();
    this.fileRemoved.emit(true);
  }
}
