import * as Yup from 'yup';

export const AccountSettingsValidation = () => {
    return Yup.object({
        firstName: Yup.string()
            .min(3, 'Must be at least 3 characters')
            .max(15, 'Must be 15 characters or lesse')
            .required('Required'),
        lastName: Yup.string()
            .min(3, 'Must be at least 3 characters')
            .max(15, 'Must be 15 characters or lesse')
            .required('Required'),
        email: Yup.string()
            .email('Invalid email')
            .required('Required'),
        password: Yup.string().required('Required'),
        passwordConfirmation: Yup.string()
            .oneOf([Yup.ref('password'), null], 'Passwords must match')
    })
}
