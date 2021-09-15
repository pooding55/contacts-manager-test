<x-layouts.app>
    <div class="container">
        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif
        @if(session()->has('error_message'))
            <div class="alert alert-danger">
                {{ session()->get('error_message') }}
            </div>
        @endif

        <div class="col-xs-12 text-center mb-5">
            <form action="{{ route('button.click') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger">
                    Click to simple button
                </button>
            </form>
        </div>

        <div class="col-xs-12 text-center mb-5">
            <p>Import from file</p>
            <form action="{{ route('contacts.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input name="document" type="file">
                <button type="submit" class="btn btn-danger">
                    Import
                </button>
            </form>
        </div>

        <a href="{{ route('contacts.create') }}" class="btn btn-primary">Create contact</a>
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($contacts as $contact)
                    <tr>
                        <th scope="row">{{ $contact->id }}</th>
                        <td>{{ $contact->first_name }}</td>
                        <td>{{ $contact->last_name }}</td>
                        <td>{{ $contact->email }}</td>
                        <td>{{ $contact->phone }}</td>
                        <td class="action">
                            <div>
                                <form action="{{ route('contacts.destroy', $contact->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </div>

                            <div>
                                <a href="{{ route('contacts.edit', $contact->id) }}" class="btn btn-primary">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $contacts->links() }}
    </div>
</x-layouts.app>
