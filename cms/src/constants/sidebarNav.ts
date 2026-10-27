import {
  LayoutDashboard, Users, Building2, Star, Home,
  Tag, FileText, Mail, Settings, Megaphone,
  type LucideIcon,
} from 'lucide-react'
import { ROUTES } from '@/constants/routes'

export interface NavChild {
  label: string
  to: string
}

export interface NavItemConfig {
  label: string
  icon: LucideIcon
  to?: string
  children?: NavChild[]
}

export const NAV_ITEMS: NavItemConfig[] = [
  { label: 'Dashboard', icon: LayoutDashboard, to: ROUTES.dashboard },
  { label: 'Users', icon: Users, to: ROUTES.users.list },
  {
    label: 'Company Users', icon: Building2, children: [
      { label: 'Approved Company Users', to: ROUTES.builder.approved },
      { label: 'Pending Company Users', to: ROUTES.builder.pending },
    ],
  },
  {
    label: 'Company', icon: Building2, children: [
      { label: 'Company Approved', to: ROUTES.company.approved },
      { label: 'Company Pending', to: ROUTES.company.pending },
    ],
  },
  {
    label: 'Review', icon: Star, children: [
      { label: 'Review Approved', to: ROUTES.reviews.approved },
      { label: 'Review Pending', to: ROUTES.reviews.pending },
      { label: 'Review Inactive', to: ROUTES.reviews.inactive },
    ],
  },
  {
    label: 'Project', icon: Home, children: [
      { label: 'Project Approved', to: ROUTES.properties.approved },
      { label: 'Project Pending', to: ROUTES.properties.pending },
    ],
  },
  {
    label: 'Advertises', icon: Megaphone, children: [
      { label: 'Approved Advertises', to: ROUTES.advertises.approved },
      { label: 'Pending Advertises', to: ROUTES.advertises.pending },
    ],
  },
  {
    label: 'Project Setting', icon: Settings, children: [
      { label: 'Project Category', to: ROUTES.propertySettings.categories.list },
      { label: 'Manage Rating', to: ROUTES.propertySettings.manageRating },
    ],
  },
  {
    label: 'Blogs', icon: FileText, children: [
      { label: 'Blog Category', to: ROUTES.blogs.categories.list },
      { label: 'Blogs', to: ROUTES.blogs.list },
    ],
  },
  {
    label: 'CMS', icon: FileText, children: [
      { label: 'Home', to: ROUTES.cms.home },
      { label: 'About', to: ROUTES.cms.about },
      { label: 'Property Guide', to: ROUTES.cms.propertyGuide },
      { label: 'Property Blog', to: ROUTES.cms.propertyCoach },
      { label: 'Contact', to: ROUTES.cms.contact },
      { label: 'Reviews Listing', to: ROUTES.cms.reviewsListing },
      { label: 'Review Guidelines', to: ROUTES.cms.reviewGuidelines },
      { label: 'Terms of Use', to: ROUTES.cms.termsOfUse },
      { label: 'Privacy Policy', to: ROUTES.cms.privacyPolicy },
    ],
  },
  { label: 'Rating Setting', icon: Star, to: ROUTES.rating.settings },
  { label: 'Manage Tags', icon: Tag, to: ROUTES.tags },
  { label: 'Contact Us', icon: Mail, to: ROUTES.contactUs },
]
