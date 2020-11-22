interface IUserBase {
  firstName: string;
  lastName: string;
  email: string;
  password: string;
}
export interface IRegisterIn extends IUserBase {
  passwordConfirmation: string;
}
export interface ILoginIn {
  email: string;
  password: string;
}
export interface IAuthInfo {
  email: string;
  jwt: string;
}
export interface IUserSettings extends IUserBase {
  oldPassword: string;
}
