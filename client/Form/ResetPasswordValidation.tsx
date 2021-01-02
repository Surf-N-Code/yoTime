import * as Yup from 'yup';

export const ResetPasswordValidation = () => {
    return Yup.object({
        email: Yup.string()
            .email('Invalid email')
            .required('Required'),
    })
}
