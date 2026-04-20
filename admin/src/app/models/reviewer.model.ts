export interface ReviewerModel {
  id: number | null;
  firstname: string;
  lastname: string;
  email: string;
  mobile: string;
  profile_image: string | null;
  full_profile_image: string | null;
  location: string | null;
  status: string | null;
}
