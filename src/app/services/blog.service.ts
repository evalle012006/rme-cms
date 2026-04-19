import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class BlogService {

  constructor(private http: HttpClient) {
  }

  addBlogCategory(postValue) {
    return this.http.post(environment.api_url + 'blog-category-add', postValue);
  }

  getBlogCategoryById(id) {
    return this.http.get(environment.api_url + 'blog-category-get/' + id);
  }

  updateBlogCategory(postValues) {
    return this.http.post(environment.api_url + 'blog-category-update', postValues);
  }


  addBlog(postValue) {
    return this.http.post(environment.api_url + 'blog-add', postValue);
  }

  getBlogById(id) {
    return this.http.get(environment.api_url + 'blog-get/' + id);
  }

  updateBlog(postValues) {
    return this.http.post(environment.api_url + 'blog-update', postValues);
  }

  getAllBlogCategory() {
    return this.http.get(environment.api_url + 'get-all-blog-category');
  }
  deleteBlog(id) {
    return this.http.post<any>(environment.api_url + 'blog-delete', {id: id});
  }
}
