import { INavData } from '@coreui/angular';

export const navItems: INavData[] = [
  {
    name: 'Dashboard',
    url: '/dashboard',
    icon: 'icon-speedometer',
  },
  {
    name: 'Users',
    url: '/users',
    icon: 'icon-user',
  },
  {
    name: 'Company Users',
    url: '/builder',
    icon: 'fa fa-building',
    children: [
      {
        name: 'Approved Company Users',
        url: '/builder/approve',
      },
      {
        name: 'Pending Company Users',
        url: '/builder/pending',
      },
    ],
  },
  {
    name: 'Company',
    url: '/company',
    icon: 'fa fa-building',
    children: [
      {
        name: 'Company Approved',
        url: '/company/approve',
      },
      {
        name: 'Company Pending',
        url: '/company/pending',
      },
    ],
  },
  {
    name: 'Review',
    url: '/review',
    icon: 'fa fa-star',
    children: [
      {
        name: 'Review Approved',
        url: '/review/approve',
      },
      {
        name: 'Review Pending',
        url: '/review/pending',
      },
    ],
  },
  {
    name: 'Property',
    url: 'property',
    icon: 'fas fa-city',
    children: [
      {
        name: 'Property Approved',
        url: '/property/approve',
      },
      {
        name: 'Property Pending',
        url: '/property/pending',
      },
    ],
  },
  {
    name: 'Advertises',
    url: 'advertise',
    icon: 'fa fa-sticky-note',
    children: [
      {
        name: 'Approved Advertises',
        url: '/advertise/approve',
      },
      {
        name: 'Pending Advertises',
        url: '/advertise/pending',
      },
    ],
  },
  {
    name: 'Property Setting',
    url: 'property-category',
    icon: 'fa fa-cog',
    children: [
      {
        name: 'Property Category',
        url: '/property-category',
      },
      {
        name: 'Manage Rating',
        url: '/property-category/manage-rating',
      },
    ],
  },
  {
    name: 'Blogs',
    url: 'blog',
    icon: 'fa fa-blog',
    children: [
      {
        name: 'Blog Category',
        url: '/blog-category',
      },
      {
        name: 'Blogs',
        url: '/blogs',
      },
    ],
  },
  {
    name: 'Cms',
    url: 'cms',
    icon: 'fa fa-file',
    children: [
      {
        name: 'Home',
        url: '/cms/home',
      },
      {
        name: 'About',
        url: '/cms/about',
      },
      {
        name: 'Property Guide',
        url: '/cms/property-guide',
      },
      {
        name: 'Property Coach',
        url: '/cms/property-coach',
      },
      {
        name: 'Contact',
        url: '/cms/contact',
      },
      {
        name: 'Review Guidelines',
        url: '/cms/review-guidelines',
      },
      {
        name: 'Terms Of Use',
        url: '/cms/terms-of-use',
      },
      {
        name: 'Privacy Policy',
        url: '/cms/privacy-policy',
      },
    ],
  },
  {
    name: 'Rating Setting',
    url: '/rating',
    icon: 'fa fa-star-half-alt',
  },
  {
    name: 'Manage Tags',
    url: '/tags',
    icon: 'fa fa-tags',
  },
  {
    name: 'Contact Us',
    url: '/contact',
    icon: 'fa fa-address-book',
  },
  // {
  //   name: 'Cms',
  //   url: '/cms',
  //   icon: 'fa fa-address-book'
  // },
];
