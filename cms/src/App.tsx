import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'sonner'
import ScrollToTop from '@/components/ScrollToTop'
import AuthLayout from '@/components/layouts/AuthLayout'
import AuthGuard from '@/components/auth/AuthGuard'
import DefaultLayout from '@/components/layouts/DefaultLayout'
import LoginPage from '@/pages/auth/LoginPage'
import DashboardPage from '@/pages/dashboard/DashboardPage'
import UsersPage from '@/pages/users/UsersPage'
import UserAddPage from '@/pages/users/UserAddPage'
import UserEditPage from '@/pages/users/UserEditPage'
import BuilderApprovedPage from '@/pages/builders/BuilderApprovedPage'
import BuilderPendingPage from '@/pages/builders/BuilderPendingPage'
import BuilderEditPage from '@/pages/builders/BuilderEditPage'
import ReviewApprovedPage from '@/pages/reviews/ReviewApprovedPage'
import ReviewPendingPage from '@/pages/reviews/ReviewPendingPage'
import ContactUsPage from '@/pages/contact/ContactUsPage'
import ProfileSettingsPage from '@/pages/settings/ProfileSettingsPage'
import HomePage from '@/pages/cms/HomePage'
import AboutPage from '@/pages/cms/AboutPage'
import PropertyGuidePage from '@/pages/cms/PropertyGuidePage'
import PropertyCoachPage from '@/pages/cms/PropertyCoachPage'
import ContactPage from '@/pages/cms/ContactPage'
import ReviewGuidelinesPage from '@/pages/cms/ReviewGuidelinesPage'
import TermsOfUsePage from '@/pages/cms/TermsOfUsePage'
import PrivacyPolicyPage from '@/pages/cms/PrivacyPolicyPage'
import RatingSettingsPage from './pages/ratings/RatingSettingsPage'
import RatingAddPage from './pages/ratings/RatingAddPage'
import RatingEditPage from './pages/ratings/RatingEditPage'
import TagsPage from './pages/tags/TagsPage'
import CompanyApprovedPage from './pages/companies/CompanyApprovedPage'
import CompanyPendingPage from './pages/companies/CompanyPendingPage'
import CompanyAddPage from './pages/companies/CompanyAddPage'
import CompanyEditPage from './pages/companies/CompanyEditPage'
import PropertyCategoriesPage from './pages/property-settings/PropertyCategoriesPage'
import CategoryAddPage from './pages/property-settings/CategoryAddPage'
import CategoryEditPage from './pages/property-settings/CategoryEditPage'
import ManageRatingPage from './pages/property-settings/ManageRatingPage'
import AdvertiseApprovedPage from './pages/advertises/AdvertiseApprovedPage'
import AdvertisePendingPage from './pages/advertises/AdvertisePendingPage'
import PropertyPendingPage from './pages/properties/PropertyPendingPage'
import PropertyApprovedPage from './pages/properties/PropertyApprovedPage'
import PropertyAddPage from './pages/properties/PropertyAddPage'
import PropertyEditPage from './pages/properties/PropertyEditPage'
import BlogCategoriesPage from './pages/blogCategories/BlogCategoriesPage'
import BlogCategoryAddPage from './pages/blogCategories/BlogCategoryAddPage'
import BlogCategoryEditPage from './pages/blogCategories/BlogCategoryEditPage'
import BlogsListPage from './pages/blogs/BlogsListPage'
import BlogAddPage from './pages/blogs/BlogAddPage'
import BlogEditPage from './pages/blogs/BlogEditPage'
import { ROUTES } from '@/constants/routes'

export default function App() {
  return (
    <BrowserRouter>
      <ScrollToTop />
      <Toaster position="top-right" richColors />

      <Routes>
        {/* Unauthenticated routes — centered card shell, redirects to /dashboard if already logged in */}
        <Route element={<AuthLayout />}>
          <Route path={ROUTES.login} element={<LoginPage />} />
          {/* Future: <Route path="/forgot-password" element={<ForgotPasswordPage />} /> */}
          {/* Future: <Route path="/reset-password" element={<ResetPasswordPage />} /> */}
        </Route>

        {/* Authenticated routes — sidebar + top bar, redirects to /login if logged out */}
        <Route element={<AuthGuard />}>
          <Route element={<DefaultLayout />}>
            <Route path={ROUTES.dashboard} element={<DashboardPage />} />

            <Route path={ROUTES.settings.profile} element={<ProfileSettingsPage />} />

            <Route path={ROUTES.users.list} element={<UsersPage />} />
            <Route path={ROUTES.users.add} element={<UserAddPage />} />
            <Route path={ROUTES.users.editPattern} element={<UserEditPage />} />

            <Route path={ROUTES.builder.approved} element={<BuilderApprovedPage />} />
            <Route path={ROUTES.builder.pending} element={<BuilderPendingPage />} />
            <Route path={ROUTES.builder.editPattern} element={<BuilderEditPage />} />

            <Route path={ROUTES.reviews.approved} element={<ReviewApprovedPage />} />
            <Route path={ROUTES.reviews.pending} element={<ReviewPendingPage />} />

            <Route path={ROUTES.cms.home} element={<HomePage />} />
            <Route path={ROUTES.cms.about} element={<AboutPage />} />
            <Route path={ROUTES.cms.propertyGuide} element={<PropertyGuidePage />} />
            <Route path={ROUTES.cms.propertyCoach} element={<PropertyCoachPage />} />
            <Route path={ROUTES.cms.contact} element={<ContactPage />} />
            <Route path={ROUTES.cms.reviewGuidelines} element={<ReviewGuidelinesPage />} />
            <Route path={ROUTES.cms.termsOfUse} element={<TermsOfUsePage />} />
            <Route path={ROUTES.cms.privacyPolicy} element={<PrivacyPolicyPage />} />

            <Route path={ROUTES.contactUs} element={<ContactUsPage />} />

            <Route path={ROUTES.rating.settings} element={<RatingSettingsPage />} />
            <Route path={ROUTES.rating.add} element={<RatingAddPage />} />
            <Route path={ROUTES.rating.editPattern} element={<RatingEditPage />} />
            <Route path={ROUTES.tags} element={<TagsPage />} />

            <Route path={ROUTES.company.approved} element={<CompanyApprovedPage />} />
            <Route path={ROUTES.company.pending} element={<CompanyPendingPage />} />
            <Route path={ROUTES.company.add} element={<CompanyAddPage />} />
            <Route path={ROUTES.company.editPattern} element={<CompanyEditPage />} />

            <Route path={ROUTES.advertises.approved} element={<AdvertiseApprovedPage />} />
            <Route path={ROUTES.advertises.pending} element={<AdvertisePendingPage />} />

            <Route path={ROUTES.properties.approved} element={<PropertyApprovedPage />} />
            <Route path={ROUTES.properties.pending} element={<PropertyPendingPage />} />
            <Route path={ROUTES.properties.add} element={<PropertyAddPage />} />
            <Route path={ROUTES.properties.editPattern} element={<PropertyEditPage />} />

            <Route path={ROUTES.propertySettings.categories.list} element={<PropertyCategoriesPage />} />
            <Route path={ROUTES.propertySettings.categories.add} element={<CategoryAddPage />} />
            <Route path={ROUTES.propertySettings.categories.editPattern} element={<CategoryEditPage />} />
            <Route path={ROUTES.propertySettings.manageRating} element={<ManageRatingPage />} />

            <Route path={ROUTES.blogs.categories.list} element={<BlogCategoriesPage />} />
            <Route path={ROUTES.blogs.categories.add} element={<BlogCategoryAddPage />} />
            <Route path={ROUTES.blogs.categories.editPattern} element={<BlogCategoryEditPage />} />

            <Route path={ROUTES.blogs.list} element={<BlogsListPage />} />
            <Route path={ROUTES.blogs.add} element={<BlogAddPage />} />
            <Route path={ROUTES.blogs.editPattern} element={<BlogEditPage />} />

            <Route path={ROUTES.root} element={<Navigate to={ROUTES.dashboard} replace />} />
          </Route>
        </Route>

        {/* 404 fallback */}
        <Route path="*" element={<Navigate to={ROUTES.dashboard} replace />} />

      </Routes>
    </BrowserRouter>
  )
}
