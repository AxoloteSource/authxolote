import { Page } from '@/components/Page/Page'
import { DataTableFilter } from '@/components/Tables/DataTableFilter/DataTableFilter'
import { RoutesBackoffice } from '@/routes/modules/backoffice.routes'
import { useServiceIndexUsers } from '@/services/authxolote/users/useServiceUsers'
import { FilterFormUser } from './partials/FilterFormUser'
import { UserForm } from './partials/UserForm/UserForm'
import { useUserPage } from './useUserPage'

const breadCrumblesItems = [{ to: RoutesBackoffice.Home, children: 'home' }, { children: 'users' }]

const UserPage = () => {
  const { filters, renderersMap, isOpen, open, close, refreshKey, handleSuccess } = useUserPage()
  return (
    <Page titleTranslation="users" breadCrumblesItems={breadCrumblesItems}>
      <DataTableFilter key={refreshKey} filters={filters} onClickNew={open} service={useServiceIndexUsers} renderersMap={renderersMap}>
        {(formik) => <FilterFormUser formik={formik} />}
      </DataTableFilter>
      <UserForm onSuccess={handleSuccess} close={close} isOpen={isOpen} />
    </Page>
  )
}

export default UserPage