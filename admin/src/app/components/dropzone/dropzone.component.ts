import {Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild} from '@angular/core';
import {HelperService} from '../../services';

@Component({
  selector: 'app-dropzone',
  templateUrl: './dropzone.component.html',
  styleUrls: ['./dropzone.component.scss']
})
export class DropzoneComponent implements OnInit {

  @ViewChild('mediaInput') mediaInput: ElementRef;

  @Input() allowedFileType = ['jpg', 'png'];
  @Input() invalidFileText = '';
  @Input() placeHolderText = '';
  @Input() onDragText = '';
  @Input() previewImages: { id: any, path: string }[] = [];

  @Output() fileHandle = new EventEmitter();
  @Output() fileSelectionInitiated = new EventEmitter();
  @Output() fileRemoved = new EventEmitter();

  public fileDragOver: boolean = false;

  constructor(private _helperService: HelperService) {

  }

  ngOnInit(): void {
  }

  onMediaHandle(files) {
    this.fileSelectionInitiated.emit(files.length);
    for (let i = 0; i < files.length; i++) {
      const fileToUpload = files.item(i);
      if (fileToUpload) {
        if (fileToUpload['name'] !== undefined || fileToUpload['name'] !== null) {
          const fileExt = fileToUpload['name'].split('.').pop();
          if (this.allowedFileType.includes(fileExt.toLowerCase())) {
            this.fileHandle.emit(fileToUpload);
            this.setImagePreview(fileToUpload);
          } else {
            this.fileHandle.emit(null);
            // this._toaster.pop('warning', this.invalidFileText);
          }
        }
      }
    }
    this.mediaInput.nativeElement.value = '';
  }

  async setImagePreview(file) {
    const result: any = await this._helperService.fileToBase64(file);
    if (result instanceof Error) {
      return;
    }
    this.previewImages.push({id: 0, path: result});
  }

  onImageRemove(id, index) {
    this.previewImages.splice(index, 1);
    this.fileRemoved.emit({id, index});
  }

}
