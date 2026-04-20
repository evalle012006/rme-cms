export interface BuilderModel {
  id: number | null;
  firstname: string;
  email: string;
  mobile: string;
  location: string | null;
  status: string | null;
  company: {
    logo: string | null;
    full_company_logo: string | null;
    company_name: string;
    contact_number: string;
    bio: string;
    website: string;
    location: string;
  };
}
