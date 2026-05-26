
import type { basicTable1 } from '@/types/components/table/shadcntable'


//Shadcn BASIC table
/*Basic Table 1*/
const basicTableData1: basicTable1[] = [
    {
        avatar: '',
        name: 'Sunil Joshi',
        post: 'Web Designer',
        pname: 'Elite Admin',
        status: 'Active',
        statuscolor: 'success',
        teams: [
            {
                id: '1',
                color: 'destructive',
                text: 'S'
            },
            {
                id: '2',
                color: 'secondary   ',
                text: 'D'
            }
        ],
        budget: '$3.9'
    },
    {
        avatar: '',
        name: 'Andrew McDownland',
        post: 'Project Manager',
        pname: 'Real Homes WP Template',
        status: 'Pending',
        statuscolor: 'warning',
        teams: [
            {
                id: '1',
                color: 'secondary',
                text: 'N'
            },
            {
                id: '2',
                color: 'warning   ',
                text: 'X'
            },
            {
                id: '3',
                color: 'primary   ',
                text: 'A'
            }
        ],
        budget: '$24.5k'
    },
    {
        avatar: '',
        name: 'Christopher Jamil',
        post: 'Project Manager',
        pname: 'MedicalPro WP Template',
        status: 'Completed',
        statuscolor: 'primary',
        teams: [
            {
                id: '1',
                color: 'secondary',
                text: 'X'
            }
        ],
        budget: '$12.8k'
    },
    {
        avatar: '',
        name: 'Nirav Joshi',
        post: 'Frontend Engineer',
        pname: 'Hosting Press HTML',
        status: 'Active',
        statuscolor: 'success',
        teams: [
            {
                id: '1',
                color: 'primary',
                text: 'X'
            },
            {
                id: '2',
                color: 'destructive',
                text: 'Y'
            }
        ],
        budget: '$2.4k'
    },
    {
        avatar: '',
        name: 'Micheal Doe',
        post: 'Content Writer',
        pname: 'Helping Hands WP Template',
        status: 'Cancel',
        statuscolor: 'destructive',
        teams: [
            {
                id: '1',
                color: 'secondary',
                text: 'S'
            }
        ],
        budget: '$9.3k'
    }
];







export { basicTableData1 };
