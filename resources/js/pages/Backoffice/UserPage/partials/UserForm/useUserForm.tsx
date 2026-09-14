import { useOnSubmit } from '@/hooks/useOnSubmit'
import { useServiceStoreUser } from '@/services/authxolote/users/useServiceUsers'
import { useTranslation } from 'react-i18next'
import * as Yup from 'yup'
import { es } from 'yup-locales'
Yup.setLocale(es)

export interface IInitialValuesUser {
  name: string
  email: string
  role_id: string
  password: string
}

const initialValues: IInitialValuesUser = {
  name: '',
  email: '',
  role_id: '',
  password: ''
}

interface IUseUserFormProps {
  onSuccess?: () => void
}

export const useUserForm = ({ onSuccess }: IUseUserFormProps) => {
  const { t } = useTranslation()
  const validationSchema = Yup.object().shape({
    name: Yup.string().required(t('name_required')),
    email: Yup.string().email(t('email_invalid')).required(t('email_required')),
    role_id: Yup.string().required(t('role_required')),
    password: Yup.string().min(8, t('password_min')).required(t('password_is_required'))
  })

  const mutator = useServiceStoreUser()

  const { onSubmit } = useOnSubmit<IInitialValuesUser>({
    mutateAsync: mutator.mutateAsync,
    onSuccess: async () => {
      if (onSuccess) {
        onSuccess()
      }
    }
  })

  const formikProps = {
    validationSchema,
    initialValues,
    onSubmit
  }

  return { formikProps, t }
}