import Button from '@/components/Buttons/Button'
import { ButtonTypeEnum } from '@/components/Buttons/enums/buttonType.enum'
import { Input } from '@/components/Form/Input'
import { InputTypeEnum } from '@/components/Form/Input/InputType.enum'
import InputSelect from '@/components/Form/Select/Select'
import { IOptions } from '@/components/Form/Select/interfaces/IOptions'
import Modal from '@/components/Modal/Modal'
import { Color } from '@/enums/Color'
import { useServiceIndexRoles } from '@/services/authxolote/roles/useServiceRoles'
import { Form, Formik } from 'formik'
import { useMemo } from 'react'
import { IInitialValuesUser, useUserForm } from './useUserForm'

interface IUserFormProps {
  onSuccess?: () => void
  close: () => void
  isOpen: boolean
}

export const UserForm = ({ close, isOpen, onSuccess }: IUserFormProps) => {
  const { formikProps, t } = useUserForm({ onSuccess })
  const { data: rolesData, isLoading: rolesLoading } = useServiceIndexRoles({ page: 1, limit: 100 })

  const roleOptions = useMemo<IOptions[]>(
    () => (rolesData?.data ?? []).map((role) => ({ value: role.id, label: role.name })),
    [rolesData]
  )

  return (
    <Modal className="w-full max-w-lg" title={t('new_user')} isOpen={isOpen} close={close} closeOnOverlayClick={false}>
      <Formik enableReinitialize {...formikProps}>
        {(formik) => (
          <Form className="mb-4 grid grid-cols-12 gap-3">
            <Input<IInitialValuesUser> className="col-span-12" name="name" type={InputTypeEnum.Text} label={`${t('name')}`} formik={formik} />
            <Input<IInitialValuesUser> className="col-span-12" name="email" type={InputTypeEnum.Email} label={`${t('email')}`} formik={formik} />
            <InputSelect<IInitialValuesUser> className="col-span-12" name="role_id" label={`${t('role')}`} formik={formik} options={roleOptions} isLoading={rolesLoading} />
            <Input<IInitialValuesUser> className="col-span-12" name="password" type={InputTypeEnum.Password} label={`${t('password')}`} formik={formik} />
            <div className="col-span-12 mt-3 flex justify-between gap-3">
              <Button onClick={close} type={ButtonTypeEnum.Button} color={Color.White}>
                {t('cancel')}
              </Button>
              <Button type={ButtonTypeEnum.Submit} disabled={formik.isSubmitting}>
                {t('save')}
              </Button>
            </div>
          </Form>
        )}
      </Formik>
    </Modal>
  )
}